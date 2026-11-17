export const meta = {
  name: 'implement-integration-core',
  description: 'Take a Jira ticket from analysis to a draft PR in sequra/integration-core: plans it with the integration-core-planner agent, implements it with tests with the integration-core-executor agent, then runs a bounded gate->review->verify->re-implement loop. The gate is .claude/workflows/pre-pr-gate.sh (PHP 7.2 syntax and phpcs on the changed files, the full phpstan and the full phpunit suite, in the worktree container), and it must be green before each review. Round 1 adds a simplify dimension that changes no file: it reviews the change against the plan settled decisions, and its findings go through the same adversarial verify as every other finding, so only confirmed ones reach the executor. The workflow pushes only the tree the last review round saw green and opens the PR. With args.entry fix it works on the open PR instead: it fixes the cause that the fix context file names, gates and reviews the delta, and publishes with a plain push.',
  whenToUse: 'Invoke with args = { ticketId: "LIS-123" } from the root of an integration-core worktree whose php container is running, to implement a Jira ticket end to end. Optional args: inWorktree (true when the worktree is already on its feature branch, default false), baseBranch (the branch the work starts from and the PR stacks on, default master), ticketRead (how to read the ticket, default acli), label (PR label), maxRounds (max review passes), model and effort (override the model or the effort of every agent call), previousAttemptsFile (the rendered history of earlier autonomous attempts; every agent is told to read it), entry (ticket, the default, or fix), fixContextFile (required with entry fix). The epic-runner passes these from .claude/epic-runner.json.',
  phases: [
    { title: 'Analyze' },
    { title: 'Branch' },
    { title: 'Implement' },
    { title: 'Gate' },
    { title: 'Review' },
    { title: 'Verify' },
    { title: 'PR' },
    { title: 'Publish' },
  ],
  entries: ['ticket', 'fix'],
}

const ARGS = typeof args === 'string' ? (args ? JSON.parse(args) : {}) : (args || {})
const RAW_TICKET = ARGS.ticketId || ''
const _ticketMatch = RAW_TICKET.match(/\b([A-Z]+-\d+)\b/)
const TICKET = _ticketMatch ? _ticketMatch[1] : RAW_TICKET
const PR_LABEL = ARGS.label || 'Draft - Do not Review'
const ENTRY = ARGS.entry === undefined ? 'ticket' : ARGS.entry
if (!['ticket', 'fix'].includes(ENTRY)) throw new Error('implement does not know args.entry ' + JSON.stringify(ENTRY) + ': use ticket or fix')
const FIX_CONTEXT_FILE = typeof ARGS.fixContextFile === 'string' ? ARGS.fixContextFile : ''
if (ENTRY !== 'ticket' && !FIX_CONTEXT_FILE) throw new Error('implement with args.entry ' + ENTRY + ' requires args.fixContextFile')
const MAX_ROUNDS = ARGS.maxRounds || (ENTRY === 'ticket' ? 3 : 2)
const TIERS = {
  mechanical: { model: 'sonnet', effort: 'low' },
  deep: { model: 'opus', effort: 'medium' },
}
const MODEL_OVERRIDE = typeof ARGS.model === 'string' ? ARGS.model : ''
const EFFORT_OVERRIDE = typeof ARGS.effort === 'string' ? ARGS.effort : ''
function tier(name) { return { model: MODEL_OVERRIDE || TIERS[name].model, effort: EFFORT_OVERRIDE || TIERS[name].effort } }
function required(result, step) {
  if (result) return result
  throw new Error('Aborting — the ' + step + ' step returned no result: its agent died on a terminal API error (usage limit or network); nothing more can be trusted this run.')
}

if (!TICKET) throw new Error('implement requires args.ticketId (e.g. { ticketId: "LIS-123" })')

const PROJECT = typeof ARGS.repo === 'string' && ARGS.repo ? ARGS.repo : 'integration-core'
const IN_WORKTREE = ARGS.inWorktree === true
const BASE_BRANCH = ARGS.baseBranch || 'master'
const TICKET_READ = typeof ARGS.ticketRead === 'string' && ARGS.ticketRead ? ARGS.ticketRead : '`acli jira workitem view ' + TICKET + '`'
const PREVIOUS_ATTEMPTS_FILE = ARGS.previousAttemptsFile || ''
const PREVIOUS_ATTEMPTS = PREVIOUS_ATTEMPTS_FILE
  ? 'This ticket has been attempted autonomously before, and ' + PREVIOUS_ATTEMPTS_FILE + ' records every earlier attempt: what it tried, whether it delivered, and any comment a human wrote after it. Read that file before you decide anything, and do not repeat an approach it marks as one that did not deliver. If a human comment there states a decision, follow it. If every listed approach failed and you have no new hypothesis, say so in the output this prompt asks you for — a human then takes over.'
  : ''
const TICKET_LINE = 'Ticket under work: ' + TICKET + '.'
const SHARED = [TICKET_LINE, PREVIOUS_ATTEMPTS].filter(Boolean).join('\n\n')

const PLANNER_AGENT = 'integration-core-planner'
const EXECUTOR_AGENT = 'integration-core-executor'
const REVIEWER_AGENT = 'integration-core-reviewer'
const SIMPLIFY_KEY = 'simplify'

const ANALYSIS_SCHEMA = {
  type: 'object',
  additionalProperties: false,
  required: ['summary', 'description', 'acceptance', 'design', 'plan', 'acceptanceCriteria', 'blocked'],
  properties: {
    summary: { type: 'string', minLength: 40, description: 'What the ticket asks for, 2-5 sentences. One field per parameter — never nest other fields inside this string' },
    blocked: { type: 'boolean', description: 'true when the ticket itself could not be read (Jira unreachable, key not found, no permission). Never invent a plan in that case' },
    blockedReason: { type: 'string', description: 'When blocked is true: what failed, and the exact command or credential a human must fix. Empty otherwise' },
    description: { type: 'string', minLength: 120, description: 'The plan description content (What, Why, Technical Scope)' },
    acceptance: { type: 'string', minLength: 40, description: 'The plan acceptance content (checklist of acceptance criteria)' },
    design: { type: 'string', minLength: 80, description: 'The plan design content (Settled Decisions, Reference Patterns, Implementation Steps, Verification)' },
    notes: { type: 'string', description: 'The plan notes content (Risks, Open Questions, Legacy Patterns)' },
    plan: { type: 'string', minLength: 80, description: 'The implementation plan as one markdown document for the PR description: a "## Changes" section with one "### <n>. <title>" subsection per change, then a "## Testing" section' },
    assumptions: { type: 'array', items: { type: 'string' }, description: 'Best-judgement decisions for ambiguities in the ticket that have a reasonable default. The run proceeds on them and the PR lists them. Empty if none' },
    acceptanceCriteria: { type: 'array', minItems: 1, items: { type: 'string', minLength: 3 }, description: 'One entry per acceptance criterion, plain text' },
    relevantPaths: { type: 'array', items: { type: 'string' }, description: 'Files likely involved' },
  },
}

const BRANCH_SCHEMA = {
  type: 'object',
  additionalProperties: false,
  required: ['branch', 'onFeatureBranch'],
  properties: {
    branch: { type: 'string', description: 'The feature branch now checked out' },
    onFeatureBranch: { type: 'boolean', description: 'true if HEAD is on a non-master feature branch' },
    notes: { type: 'string' },
  },
}

const IMPL_SCHEMA = {
  type: 'object',
  additionalProperties: false,
  required: ['summary', 'filesChanged'],
  properties: {
    summary: { type: 'string', description: ENTRY === 'ticket' ? 'What the branch implements for the ticket as it now stands, 2-5 sentences: the whole change, not only the last pass' : 'What this run changed over the pushed pull request, 2-5 sentences: every pass of the run, not only the last, and not the rest of the branch' },
    filesChanged: { type: 'array', items: { type: 'string' }, description: 'Repo-relative paths created or edited' },
    acceptanceMet: { type: 'array', items: { type: 'string' }, description: 'Each acceptance criterion and whether met' },
    keyDecisions: { type: 'string', description: 'Notable implementation choices for reviewers / next round' },
    issues: { type: 'string', description: 'Anything left broken or uncertain; empty string if none' },
  },
}

const GATE_SCHEMA = {
  type: 'object',
  additionalProperties: false,
  required: ['exitCode', 'output'],
  properties: {
    exitCode: { type: 'integer', description: 'The exit code of the gate command' },
    output: { type: 'string', description: 'Everything the gate command printed, verbatim' },
  },
}

const REVIEW_SCHEMA = {
  type: 'object',
  additionalProperties: false,
  required: ['verdict', 'findings'],
  properties: {
    verdict: { type: 'string', description: 'PASS or NEEDS_FIX with a one-line reason' },
    findings: {
      type: 'array',
      items: {
        type: 'object',
        additionalProperties: false,
        required: ['severity', 'file', 'title', 'detail', 'suggestedFix'],
        properties: {
          severity: { type: 'string', enum: ['critical', 'improvement', 'cleanup'], description: 'critical: breaks behaviour, data or a criterion of the ticket; improvement: works, but a reviewer would ask for it before merge; cleanup: leftovers that need no change before merge' },
          file: { type: 'string' },
          line: { type: ['integer', 'null'] },
          title: { type: 'string' },
          detail: { type: 'string' },
          suggestedFix: { type: 'string' },
        },
      },
    },
  },
}

const VERDICT_SCHEMA = {
  type: 'object',
  additionalProperties: false,
  required: ['isReal', 'fixableByAgent', 'reason', 'leftOut'],
  properties: {
    isReal: { type: 'boolean', description: 'true if the finding is a genuine defect worth fixing' },
    fixableByAgent: { type: 'boolean', description: 'true if a coding agent can resolve it by editing this repo; false when it needs a human step (a product decision, a change to a decision the ticket made, another system)' },
    reason: { type: 'string' },
    refinedFix: { type: 'string', description: 'The concrete fix if real, else empty' },
    leftOut: { type: 'string', description: 'When the finding is real: the part of the finding or of its suggestedFix that refinedFix does not fix, and why; empty when refinedFix fixes all of it or the finding is not real' },
  },
}

const PR_SCHEMA = {
  type: 'object',
  additionalProperties: false,
  required: ['prUrl', 'branch', 'created'],
  properties: {
    created: { type: 'boolean', description: 'true if a PR was successfully created' },
    prUrl: { type: 'string', description: 'URL of the created PR, empty if not created' },
    branch: { type: 'string', description: 'The branch that was pushed' },
    notes: { type: 'string', description: 'Anything the CI-monitoring step should know' },
  },
}

const FIX_SCHEMA = {
  type: 'object',
  additionalProperties: false,
  required: ['resolved', 'ciVerdict', 'summary', 'filesChanged', 'replies'],
  properties: {
    resolved: { type: 'boolean', description: 'true iff the cause of the fix context file is addressed in the tree' },
    summary: { type: 'string', description: 'What you changed, or why you stopped' },
    filesChanged: { type: 'array', items: { type: 'string' }, description: 'Repo-relative paths created or edited in this pass' },
    ciVerdict: { type: 'string', enum: ['flaky', ''], description: 'flaky only when the failing check is not caused by the diff, as the fix context file says' },
    replies: {
      type: 'array',
      items: {
        type: 'object',
        additionalProperties: false,
        required: ['ref', 'kind', 'body'],
        properties: {
          ref: { type: 'integer' },
          kind: { type: 'string', enum: ['changed', 'answer', 'defer'] },
          body: { type: 'string' },
          files: { type: 'array', items: { type: 'string' } },
        },
      },
    },
  },
}

const PUBLISH_SCHEMA = {
  type: 'object',
  additionalProperties: false,
  required: ['committed', 'pushed', 'stampRefused'],
  properties: {
    committed: { type: 'boolean', description: 'true iff the work of this run is committed on the branch; also true when nothing was left to stage because HEAD already holds that work' },
    pushed: { type: 'boolean', description: 'true iff git push origin HEAD succeeded' },
    sha: { type: 'string', description: 'HEAD sha after the commit' },
    blockedCommand: { type: 'string', description: 'The verbatim command that was refused or failed; empty string if none' },
    stampRefused: { type: 'boolean', description: 'true iff the --head --stamp-only check exited non-zero' },
    notes: { type: 'string', description: 'Any detail, the stamp check output when it refused the push; empty string when everything ran' },
  },
}

const MAX_GATE_RETRIES = 2
const GATE_TIMEOUT_MS = 600000
const GATE_SCRIPT = 'bash .claude/workflows/pre-pr-gate.sh'
const GATE_CHECKS = 'the PHP 7.2 syntax check and phpcs on the changed PHP files, the full phpstan analysis and the full phpunit suite, in the php container of this worktree'
const GATE_ONCE = 'First run `docker compose exec -T php vendor/bin/phpcbf --standard=.phpcs.xml.dist <each PHP file you changed>`, as the pre-commit hook does at commit time. '
  + 'Then run the pre-PR gate once: `' + GATE_SCRIPT + '`, with timeout ' + GATE_TIMEOUT_MS + ' on the Bash tool. '
  + 'It runs ' + GATE_CHECKS + ', so run no full suite, phpcs or phpstan before it. '
  + 'Never run it a second time: report a red result in issues and return, the workflow retries it. '
  + 'If it exits 2, the gate could not run: report it and stop.'
const FIX_CONTEXT = 'The fix context file ' + FIX_CONTEXT_FILE + ' holds the rules of this run, its result contract and its context: the ticket with its description, the pull request, the base branch and the cause. Read it in full before you act, and follow its rules.'
const TRIAGE_LEFT_OUT = 'If refinedFix does not fix all of what the finding or its suggestedFix asks for, say in leftOut which part it leaves out and why. The run reports every rejected or narrowed finding to a human.'

function executorOptions(label) {
  return { agentType: EXECUTOR_AGENT, ...tier('deep'), phase: 'Implement', label: label, schema: IMPL_SCHEMA }
}

async function runGate(label, command) {
  return required(await agent([
    'Run exactly this command in the repo root and report what it printed; change nothing and fix nothing:',
    '  ' + command,
    'It runs ' + GATE_CHECKS + ', which take up to 10 minutes: pass timeout ' + GATE_TIMEOUT_MS + ' to the Bash tool',
    'so the call is not cut off at the default 2 minutes, and never retry or re-run it yourself.',
    'Return its exit code as exitCode and everything it printed, verbatim, as output.',
  ].join('\n'), { ...tier('mechanical'), phase: 'Gate', label: label, schema: GATE_SCHEMA }), 'gate')
}

function gateVerdict(gate) {
  var trailer = (String(gate.output).match(/^pre-pr-gate: (GREEN|RED)\b/m) || [])[1]
  return trailer ? trailer.toLowerCase() : 'unavailable'
}

function settledDecisions(analysis) {
  if (!analysis) {
    return 'DECISIONS ALREADY MADE: the ticket in the fix context file (ticketDescription), its Technical Approach, its Testing notes and the decisions its comments record. They are fixed for this run.'
  }
  return [
    'DECISIONS ALREADY MADE — fixed for this run. The plan below and the ticket it comes from decided them; read the ticket with ' + TICKET_READ + ' when a finding touches its Technical Approach, its Testing notes or a decision in its comments.',
    '### Design',
    analysis.design,
    '### Notes',
    analysis.notes || 'None',
    '### Acceptance criteria',
    JSON.stringify(analysis.acceptanceCriteria || []),
    '### Assumptions the run made',
    JSON.stringify(analysis.assumptions || []),
  ].join('\n')
}

function simplifyDimension(decisions) {
  return {
    key: SIMPLIFY_KEY,
    prompt: [
      'DIMENSION: Simplify. Invoke the `simplify` skill with the Skill tool over this ticket\'s change: the commits in origin/' + BASE_BRANCH + '..HEAD plus the working tree.',
      'Run only its review, its Phase 0 and Phase 1 over the reuse, simplification, efficiency and altitude angles. Skip its Phase 2: change no file, and return each finding through the schema instead.',
      'Keep behaviour exactly as it is. A change that undoes one of the decisions below is not a finding, even when it makes the code smaller: duplication the plan chose, a parameter or signature the ticket names, the place a test lives and the way its fixtures are written all stay.',
      'A finding may name a file outside the diff when its fix needs one: an existing helper or test double to reuse instead of a copy, or code this change left with no caller.',
      'Mark as improvement what you would change before merge; mark the rest cleanup, which the run does not apply.',
      'If the Skill tool offers no `simplify` skill, return the verdict "PASS — no simplify skill" and no findings.',
      '',
      decisions,
    ].join('\n'),
  }
}

function reviewDimensions(round, decisions) {
  var dimensions = [
    {
      key: 'tests',
      prompt: 'DIMENSION: Test correctness & coverage. Read .claude/docs/unitTests.md, then the tests that cover the diff (tests/ mirrors src/); the gate already ran them. '
        + 'Confirm every code change has a test, assertions are meaningful (not tautological), test doubles follow unitTests.md, '
        + 'and no test reaches the network or depends on the order other tests run in. Flag a test that departs from how the decisions below say tests are written.',
    },
    {
      key: 'correctness-acceptance',
      prompt: 'DIMENSION: Correctness & acceptance coverage. For each acceptance criterion, confirm the code satisfies it as written: the names, signatures and observable results it states. '
        + 'Check the edge cases and that nothing out of scope changed. Flag unmet criteria, mis-wiring, and code that departs from a decision below.',
    },
    {
      key: 'architecture',
      agentType: REVIEWER_AGENT,
      prompt: 'DIMENSION: The invariants of this repository, as your instructions list them. '
        + (ENTRY === 'ticket'
          ? 'Review origin/' + BASE_BRANCH + '...HEAD plus the working tree. '
          : 'Review only what this run changed over the pushed pull request: `git diff origin/$(git branch --show-current)`, including uncommitted changes, not the rest of the branch. ')
        + 'Map your severities onto the schema: Blocking is critical, Should-fix is improvement, Nit is cleanup.',
    },
  ]
  if (round === 1 && ENTRY === 'ticket') dimensions.push(simplifyDimension(decisions))
  return dimensions.map(function(d) { return { key: d.key, agentType: d.agentType, prompt: d.prompt + (d.key === SIMPLIFY_KEY ? '' : '\n\n' + decisions) } })
}

function reviewOptions(d, round) {
  var options = { ...tier('deep'), phase: 'Review', label: 'review:' + d.key + ' r' + round, schema: REVIEW_SCHEMA }
  if (d.agentType) options.agentType = d.agentType
  return options
}

function decisionRule(dimensionKey) {
  if (dimensionKey === SIMPLIFY_KEY) {
    return 'This is a cleanup finding from the simplify review. It is isReal=true only when applying it keeps behaviour exactly as it is AND contradicts none of the decisions above. If it would undo a decision, return isReal=false and name that decision in reason; if it changes behaviour, return isReal=false and name the behaviour.'
  }
  return 'A finding whose fix would contradict one of the decisions above is isReal=true, fixableByAgent=false: a human must change the ticket first. Name the decision in reason.'
}

async function verifyFinding(f, i, d, round, ledger, context, decisions) {
  return agent([
    SHARED,
    context,
    '',
    'A reviewer flagged this finding in the current repo state. Adversarially verify whether it is a REAL defect that should be',
    'fixed, by reading the actual code (and running the relevant test if needed).',
    verdictDefault(ledger, f),
    'FINDING (dimension ' + d.key + ', round ' + round + '):',
    '  severity: ' + f.severity,
    '  file: ' + f.file + (f.line ? ':' + f.line : ''),
    '  title: ' + f.title,
    '  detail: ' + f.detail,
    '  suggestedFix: ' + f.suggestedFix,
    '',
    decisions,
    '',
    decisionRule(d.key),
    'Return the verdict; if real, give a concrete refinedFix and say whether a coding agent can apply it in this repo (fixableByAgent).',
    TRIAGE_LEFT_OUT,
  ].join('\n'), { ...tier('deep'), phase: 'Verify', label: 'verify:' + d.key + '#' + i + ' r' + round, schema: VERDICT_SCHEMA })
    .then(function(v) { return { finding: f, dimension: d.key, verdict: v } })
    .catch(function() { return null })
}

async function reviewRound(round, reviewBase, ledger, context, decisions) {
  var dimensions = reviewDimensions(round, decisions)
  var reviewed = await pipeline(
    dimensions,
    function(d) { return agent(reviewBase + '\n\n' + d.prompt, reviewOptions(d, round)).then(function(review) { return reviewOutcome(review, d, round) }) },
    function(review, d) { return parallel(
      ((review && review.findings) || [])
        .map(function(f, i) { return { f: f, i: i } })
        .filter(function(entry) { return entry.f.severity === 'critical' || entry.f.severity === 'improvement' })
        .map(function(entry) { return function() { return verifyFinding(entry.f, entry.i, d, round, ledger, context, decisions) } })
    ) },
  )
  var verified = reviewed.filter(Boolean).flat().filter(Boolean).filter(function(v) { return v.verdict })
  logSimplify(round, dimensions, verified)
  return { dimensions: dimensions, verified: verified }
}

function reviewOutcome(review, d, round) {
  if (!review) {
    log('Review ' + d.key + ' r' + round + ' returned nothing: its findings are missing from this round')
    return review
  }
  if (d.key === SIMPLIFY_KEY) {
    var raised = review.findings || []
    var applicable = raised.filter(function(f) { return f.severity === 'critical' || f.severity === 'improvement' })
    log('Simplify r' + round + ' raised ' + raised.length + ' finding(s), ' + applicable.length + ' sent to verify — verdict: ' + review.verdict)
  }
  return review
}

function logSimplify(round, dimensions, verified) {
  if (!dimensions.some(function(d) { return d.key === SIMPLIFY_KEY })) return
  var mine = verified.filter(function(v) { return v.dimension === SIMPLIFY_KEY })
  var confirmed = mine.filter(function(v) { return v.verdict.isReal && v.verdict.fixableByAgent })
  var rejected = mine.filter(function(v) { return !v.verdict.isReal })
  log('Simplify r' + round + ' — ' + mine.length + ' finding(s) verified, ' + confirmed.length + ' confirmed, ' + rejected.length + ' rejected'
    + (rejected.length ? ': ' + rejected.map(function(v) { return v.finding.title + ' (' + v.verdict.reason + ')' }).join('; ') : ''))
}

function findingKey(f) {
  return f.file + '|' + f.title
}

function verdictDefault(ledger, f) {
  var earlier = ledger.find(function(c) { return findingKey(c.finding) === findingKey(f) })
  if (!earlier) return 'Default to isReal=false if you cannot substantiate it.'
  return 'An earlier round confirmed this finding as real and fixable only by a human (reason: ' + earlier.verdict.reason + '). Keep isReal=true unless you can show that the current code no longer has the defect.'
}

function dedupeFindings(list) {
  var seen = {}
  return list.filter(function(c) {
    var key = findingKey(c.finding)
    if (seen[key]) return false
    seen[key] = true
    return true
  })
}

function dismissedByTriage(verified) {
  return verified.filter(function(c) { return c.dimension !== SIMPLIFY_KEY && (!c.verdict.isReal || c.verdict.leftOut) })
}

function dismissalEntry(c) {
  return { dimension: c.dimension, severity: c.finding.severity, file: c.finding.file, title: c.finding.title, triage: c.verdict.isReal ? 'narrowed' : 'rejected', reason: c.verdict.isReal ? c.verdict.leftOut : c.verdict.reason }
}

function ledgerEntry(c) {
  return { dimension: c.dimension, file: c.finding.file, title: c.finding.title, fixableByAgent: c.verdict.fixableByAgent, reason: c.verdict.reason, fix: c.verdict.refinedFix || c.finding.suggestedFix }
}

function confirmedFixes(confirmed) {
  return JSON.stringify(confirmed.map(function(c) { return { dimension: c.dimension, file: c.finding.file, title: c.finding.title, detail: c.finding.detail, fix: (c.verdict && c.verdict.refinedFix) || c.finding.suggestedFix } }), null, 2)
}

async function implementUntilGreen(impl, stage, reimplement) {
  for (var attempt = 1; ; attempt++) {
    var gate = await runGate('gate ' + stage + ' #' + attempt, GATE_SCRIPT)
    var verdict = gateVerdict(gate)
    if (verdict === 'green') {
      log('Gate ' + stage + ' green')
      return { impl: impl, red: null }
    }
    if (verdict !== 'red') throw new Error('Aborting before the push — the pre-PR gate could not run (exit ' + gate.exitCode + ', no GREEN/RED trailer in its output, so it was cut off or never ran):\n' + gate.output + '\nThe work stays in the worktree; nothing was pushed.')
    if (attempt > MAX_GATE_RETRIES) return { impl: impl, red: gate }
    log('Gate ' + stage + ' #' + attempt + ' red — sending the failures back to the executor (retry ' + attempt + '/' + MAX_GATE_RETRIES + ')')
    impl = await reimplement([
      'The pre-PR gate FAILED. Fix the cause in the code or the tests.',
      'Never silence a phpcs sniff or a phpstan error, skip a test, or edit the stamp to get past it.',
      '',
      'GATE OUTPUT:',
      gate.output,
    ].join('\n'), 'gate fix ' + stage + ' #' + attempt, impl)
  }
}

log('Project: ' + PROJECT)

function fixReport(resolved, summary, fix, filesChanged) {
  return { resolved: resolved, summary: summary, filesChanged: filesChanged, ciVerdict: (fix && fix.ciVerdict) || '', replies: (fix && fix.replies) || [] }
}

async function runFixEntry() {
  phase('Implement')
  const fix = required(await agent([
    SHARED,
    '',
    FIX_CONTEXT,
    'TASK — FIX the cause the fix context file names, and only that cause: the failing checks, the requested change or the reviewer comments. Start from the log or the comment, reproduce it, then make the smallest change that addresses it, with its test. Compose a reply for every reviewer comment and note in the context, as its rules say.',
    'The worktree may already hold partial work from an earlier run of this cause that was cut short: run git status first, build on what is there, and never discard it. The commits that `unpublishedCommits` in the fix context file lists are different: an earlier run left them unresolved, so treat them as the rules of that file say.',
    'After the change: ' + GATE_ONCE,
    'Do not commit: the Publish phase commits.',
    'If the cause is not fixable here, stop and return resolved=false with the reason, as the rules of the fix context file say.',
    'Return the structured result.',
  ].join('\n'), { ...executorOptions(ENTRY + ' ' + TICKET), schema: FIX_SCHEMA }), ENTRY)
  if (!fix.resolved) return fixReport(false, fix.summary, fix, fix.filesChanged || [])
  log(ENTRY + ' done — files: ' + (fix.filesChanged || []).join(', '))

  let impl = fix
  let filesChanged = [...(fix.filesChanged || [])]
  const reimplement = async (task, label) => {
    impl = required(await agent([SHARED, FIX_CONTEXT, 'THIS RUN SO FAR (already on disk — read git diff to see it): ' + JSON.stringify(impl), '', task, '', 'After changes: ' + GATE_ONCE, 'Do not commit: the Publish phase commits.', 'Return the structured result (summary = what this run changed over the pushed pull request, the first pass included; filesChanged = files touched in THIS pass).'].join('\n'), executorOptions(label)), 'implement')
    filesChanged = [...new Set(filesChanged.concat(impl.filesChanged || []))]
    return impl
  }
  const decisions = settledDecisions(null)

  let round = 0
  let openFindings = []
  let dismissed = []
  while (round < MAX_ROUNDS) {
    round++
    phase('Gate')
    const gated = await implementUntilGreen(impl, 'r' + round, reimplement)
    if (gated.red) return fixReport(false, 'The pre-PR gate `' + GATE_SCRIPT + '` is still red after ' + MAX_GATE_RETRIES + ' retries; the work stays in the worktree, unpushed:\n' + gated.red.output, fix, filesChanged)

    phase('Review')
    const reviewBase = [
      SHARED,
      FIX_CONTEXT,
      'Review round ' + round + ', a DELTA review of this fix: read what it changed over the pushed pull request with `git diff origin/$(git branch --show-current)`, including uncommitted changes, and the code around it.',
      'The tree already passed the deterministic pre-PR gate (' + GATE_CHECKS + '): do not re-run those tools or report what they would.',
      'Result of this run so far: ' + JSON.stringify(impl),
      'Acceptance criteria: read them from ticketDescription in the fix context file; this run must keep every one of them true.',
      openFindings.length ? 'Findings earlier rounds confirmed and carried on: do NOT re-raise one unless a fix broke the code around it. Ledger: ' + JSON.stringify(openFindings.map(ledgerEntry)) : '',
      'When a review comment in the fix context gives a reproduction (a command, a request, a sequence of steps), run it and report as a finding when it still reproduces.',
      'Review ONLY for genuine defects in your assigned dimension, in what this run changed. Be specific (file + line). Return findings (possibly empty) per the schema.',
    ].filter(Boolean).join('\n')
    const outcome = await reviewRound(round, reviewBase, openFindings, FIX_CONTEXT, decisions)
    const verified = outcome.verified
    dismissed = dedupeFindings(dismissed.concat(dismissedByTriage(verified)))
    const real = verified.filter(function(v) { return v.verdict.isReal })
    const confirmed = real.filter(function(v) { return v.verdict.fixableByAgent })
    openFindings = dedupeFindings(openFindings.concat(real.filter(function(v) { return !v.verdict.fixableByAgent })))
    log('Round ' + round + ' — ' + confirmed.length + ' confirmed fixable defect(s)')
    if (!confirmed.length) break
    if (round >= MAX_ROUNDS) {
      openFindings = dedupeFindings(openFindings.concat(confirmed))
      break
    }
    await reimplement('A review round found the CONFIRMED defects below. Apply minimal, targeted changes that resolve ONLY these.\n\nCONFIRMED DEFECTS (round ' + round + '):\n' + confirmedFixes(confirmed), 're-implement r' + round)
  }

  const finalGate = await runGate('gate final #1', GATE_SCRIPT + ' --stamp-only')
  if (gateVerdict(finalGate) !== 'green') return fixReport(false, 'The final gate `' + GATE_SCRIPT + ' --stamp-only` is red: the tree is not the one the last review round saw green; the work stays in the worktree, unpushed:\n' + finalGate.output, fix, filesChanged)

  phase('Publish')
  const published = required(await agent([
    'TASK — Publish the work of this ' + ENTRY + ' run on the branch checked out now.',
    '1. Stage the remaining changes by explicit path, never `git add -A`, EXCLUDING CLAUDE.md, CLAUDE.local.md and the .claude/ directory.',
    '2. When anything is staged, invoke the Skill tool with `sq-git:commit` and commit following it. Add the trailer `Refs: ' + TICKET + '` above the co-author line. Never pass --no-verify.',
    'When nothing is left to stage because HEAD already holds the work of this run (for example a fix run that only answers), that counts as committed=true.',
    '3. Run `' + GATE_SCRIPT + ' --head --stamp-only`. It runs no check: it only confirms the committed tree is the one the gate stamped green. Push only when it exits 0; otherwise do NOT push, and return stampRefused=true, pushed=false and its output in notes.',
    '4. Run `git push origin HEAD`. Never force-push, never push another branch, and never open, merge or change the draft state of a pull request.',
    'If the push is refused or fails, do NOT retry it: report committed, pushed=false and the command verbatim in blockedCommand. The runner finishes the push from the committed branch.',
  ].join('\n'), { ...tier('mechanical'), phase: 'Publish', label: 'publish ' + TICKET, schema: PUBLISH_SCHEMA }), 'publish')
  log('Publish done — committed: ' + published.committed + ', pushed: ' + published.pushed)
  if (!published.committed) return fixReport(false, 'The Publish phase did not commit the work; it stays in the worktree, unpushed: ' + (published.notes || published.blockedCommand || 'no detail'), fix, filesChanged)
  if (published.stampRefused) return fixReport(false, 'The stamp check `' + GATE_SCRIPT + ' --head --stamp-only` refused the committed tree: it is not the one the gate stamped green; nothing is pushed:\n' + (published.notes || ''), fix, filesChanged)
  const caveats = (openFindings.length ? ' Unresolved review findings: ' + openFindings.map(function(c) { return c.finding.file + ': ' + c.finding.title }).join('; ') + '.' : '')
    + (dismissed.length ? ' Review findings the triage rejected or narrowed: ' + dismissed.map(dismissalEntry).map(function(e) { return e.file + ': ' + e.title + ' (' + e.triage + ': ' + e.reason + ')' }).join('; ') + '.' : '')
  return fixReport(true, impl.summary + caveats, fix, filesChanged)
}

if (ENTRY !== 'ticket') return await runFixEntry()

phase('Analyze')

const PLACEHOLDER = /^(test|todo|tbd|n\/?a|none|pending|placeholder|-+|\.+)?$/i

function unusableAnalysisFields(a) {
  if (!a || typeof a !== 'object') return ['no analysis object returned']
  const bad = []
  for (const field of ['summary', 'description', 'acceptance', 'design', 'plan']) {
    const min = ANALYSIS_SCHEMA.properties[field].minLength
    const v = typeof a[field] === 'string' ? a[field].trim() : ''
    if (PLACEHOLDER.test(v)) bad.push(field + ' (' + JSON.stringify(v) + ')')
    else if (v.length < min) bad.push(field + ' (' + v.length + ' chars, need ' + min + ')')
    else if (/<\/?(summary|description|acceptance|design|notes|acceptanceCriteria)>/i.test(v)) bad.push(field + ' (contains other fields as XML tags)')
  }
  const criteria = Array.isArray(a.acceptanceCriteria) ? a.acceptanceCriteria.filter((c) => typeof c === 'string' && !PLACEHOLDER.test(c.trim())) : []
  if (!criteria.length) bad.push('acceptanceCriteria (empty)')
  return bad
}

const ANALYSIS_RETRY_NOTE = [
  '',
  'IMPORTANT — your previous attempt returned an UNUSABLE analysis: %FIELDS%.',
  'Placeholders like "test" are never acceptable. Fill EVERY schema field with the real content from your research,',
  'and pass each field as its OWN StructuredOutput parameter — do not concatenate fields inside one string or wrap them in XML-style tags.',
].join('\n')

let analysis = null
let unusableAnalysis = []
let blockedReason = ''
for (let attempt = 1; attempt <= 2; attempt++) {
  let planned = null
  try {
    planned = await agent([
      SHARED,
      'Read the ticket with ' + TICKET_READ + '.',
      attempt > 1 ? ANALYSIS_RETRY_NOTE.replace('%FIELDS%', unusableAnalysis.join('; ')) : '',
    ].join('\n'), { agentType: PLANNER_AGENT, ...tier('deep'), phase: 'Analyze', label: 'analyze ' + TICKET + (attempt > 1 ? ' (retry)' : ''), schema: ANALYSIS_SCHEMA })
  } catch (e) {
    const why = String((e && e.message) || e).replace(/\s+/g, ' ').replace(/;/g, ',').trim().slice(0, 300)
    unusableAnalysis = ['the analyze agent failed to return a plan (' + why + ')']
    log('Analyze attempt ' + attempt + ' failed — ' + unusableAnalysis.join('; '))
    continue
  }
  if (!planned) {
    unusableAnalysis = ['the analyze agent returned no plan at all']
    log('Analyze attempt ' + attempt + ' failed — ' + unusableAnalysis.join('; '))
    continue
  }
  if (planned.blocked === true) {
    blockedReason = String(planned.blockedReason || '(no reason given)').replace(/\s+/g, ' ').replace(/;/g, ',').trim().slice(0, 300)
    unusableAnalysis = ['the planner never read ' + TICKET + ' (' + blockedReason + ')']
    log('Analyze attempt ' + attempt + ' blocked — ' + blockedReason)
    break
  }
  analysis = planned
  unusableAnalysis = unusableAnalysisFields(analysis)
  if (!unusableAnalysis.length) break
  log('Analyze attempt ' + attempt + ' unusable — ' + unusableAnalysis.join('; '))
}

if (blockedReason) {
  throw new Error('Aborting — ' + TICKET + ' was never read, so nothing was planned or implemented.\nThe planner reported: ' + blockedReason +
    '\nRestore Jira access (' + TICKET_READ + ' must work), then re-queue the ticket. Nothing was branched, committed or opened.')
}

if (unusableAnalysis.length) {
  throw new Error('Aborting — the ticket analysis is UNUSABLE after 2 attempts, nothing was implemented.\nUnusable fields: ' + unusableAnalysis.join('; ') +
    '\nThe planner agent returned placeholders or near-empty content, or never produced structured output at all. Nothing was branched, committed or opened.')
}

log('Analyze done — ' + analysis.acceptanceCriteria.length + ' acceptance criteria')

var branchInfo
if (IN_WORKTREE) {
  log('In a worktree — skipping branch setup, the worktree is already on its branch')
  branchInfo = required(await agent([
    'Run these commands and report:',
    '1. git branch --show-current  — to get the current branch name',
    '2. git fetch origin',
    '3. git log origin/' + BASE_BRANCH + '..HEAD --oneline  — to check if there are commits ahead of origin/' + BASE_BRANCH,
    '',
    'If there are commits ahead of origin/' + BASE_BRANCH + ' that do NOT belong to the current ticket (' + TICKET + '),',
    'rebase onto origin/' + BASE_BRANCH + ' to drop them: git rebase origin/' + BASE_BRANCH,
    'Return the branch name and whether HEAD is on a feature branch (not master).',
  ].join('\n'), { ...tier('deep'), phase: 'Branch', label: 'verify branch', schema: BRANCH_SCHEMA }), 'branch')
} else {
  phase('Branch')
  branchInfo = required(await agent([
    SHARED,
    '',
    'CONTEXT FROM ANALYSIS: ' + JSON.stringify(analysis),
    '',
    'TASK — Put the repo on a fresh feature branch for ' + TICKET + '.',
    '1. Look for an existing branch: git branch -a | grep -i ' + TICKET,
    '2. If a matching local/remote branch already exists, check it out and reuse it.',
    '3. Otherwise create one from an up-to-date ' + BASE_BRANCH + ', named "' + TICKET + '-<brief-kebab-description>":',
    '   git fetch origin && git checkout -b ' + TICKET + '-<brief-kebab-description> origin/' + BASE_BRANCH,
    'Do NOT commit anything here. Confirm HEAD is on the feature branch (not ' + BASE_BRANCH + ') and return it.',
  ].join('\n'), { ...tier('deep'), phase: 'Branch', label: 'branch ' + TICKET, schema: BRANCH_SCHEMA }), 'branch')
}

log('Branch ready — ' + (branchInfo && branchInfo.branch) + '; on feature branch: ' + (branchInfo && branchInfo.onFeatureBranch))
if (branchInfo && branchInfo.onFeatureBranch === false) {
  throw new Error('Branch setup failed: still on master. Aborting before edits.')
}

phase('Implement')

var impl = required(await agent([
  SHARED,
  '',
  '## Implementation Plan',
  '',
  '### Description',
  analysis.description,
  '',
  '### Acceptance',
  analysis.acceptance,
  '',
  '### Design',
  analysis.design,
  '',
  '### Notes',
  analysis.notes || 'None',
  '',
  'TASK — Implement the plan above. The inline plan is the source of truth.',
  'The worktree may already hold partial work from an earlier run of this ticket that was cut short (usage limit / network): run git status and git log origin/' + BASE_BRANCH + '..HEAD first, build on what is there, and never discard it or start over.',
  'After the change: ' + GATE_ONCE,
  'Return the structured result (summary = the whole change on the branch as it now stands; filesChanged = files touched).',
].join('\n'), executorOptions('implement ' + TICKET)), 'implement')

log('Implement (round 0) done — files: ' + (impl.filesChanged || []).join(', '))

const DECISIONS = settledDecisions(analysis)

async function reimplementTicket(task, label, current) {
  return required(await agent([
    SHARED,
    'CONTEXT FROM ANALYSIS: ' + JSON.stringify(analysis),
    'PREVIOUS IMPLEMENTATION (already on disk — read git diff to see it): ' + JSON.stringify(current || impl),
    '',
    'The ticket is ALREADY implemented. Do NOT reimplement from scratch: read the current repo state (git diff + the files)',
    'and apply minimal, targeted changes that resolve ONLY what follows, while preserving the working parts and the decisions of the plan.',
    '',
    task,
    '',
    'After changes: ' + GATE_ONCE,
    'Return the structured result (summary = the whole change on the branch as it now stands; filesChanged = files touched in THIS pass).',
  ].join('\n'), executorOptions(label)), 'implement')
}

var round = 0
var openFindings = []
var dismissed = []

while (round < MAX_ROUNDS) {
  round++
  phase('Gate')
  var gated = await implementUntilGreen(impl, 'r' + round, reimplementTicket)
  impl = gated.impl
  if (gated.red) throw new Error('Aborting before the push — the pre-PR gate is still red after ' + MAX_GATE_RETRIES + ' retries:\n' + gated.red.output + '\nThe work stays in the worktree; nothing was pushed.')

  phase('Review')
  var reviewBase = [
    SHARED,
    'The ticket has been implemented (review round ' + round + '). Read the ACTUAL current state of the repo (git diff origin/' + BASE_BRANCH + '...HEAD, the working tree and the files).',
    'The tree already passed the deterministic pre-PR gate (' + GATE_CHECKS + '): do not re-run those tools or report what they would.',
    'Implementation summary: ' + JSON.stringify(impl),
    openFindings.length ? 'Findings earlier rounds confirmed as real but fixable only by a human, already carried to the PR caveats: do NOT re-raise one unless a fix broke the code around it. Ledger: ' + JSON.stringify(openFindings.map(ledgerEntry)) : '',
    'Review ONLY for genuine defects in your assigned dimension. Be specific (file + line). Do not nitpick style',
    'beyond what the tooling enforces. Return findings (possibly empty) per the schema.',
  ].filter(Boolean).join('\n')

  var outcome = await reviewRound(round, reviewBase, openFindings, '', DECISIONS)
  var verified = outcome.verified
  var reverifiedKeys = verified.map(function(v) { return findingKey(v.finding) })
  var previousLedger = openFindings
  dismissed = dedupeFindings(dismissed.concat(dismissedByTriage(verified)))
  var real = verified.filter(function(v) { return v.verdict.isReal })
  var confirmed = real.filter(function(v) { return v.verdict.fixableByAgent })
  openFindings = dedupeFindings(openFindings
    .filter(function(c) { return !reverifiedKeys.includes(findingKey(c.finding)) })
    .concat(real.filter(function(v) { return !v.verdict.fixableByAgent })))
  var ledgerKeys = openFindings.map(function(c) { return findingKey(c.finding) })
  var dropped = previousLedger.filter(function(c) { return !ledgerKeys.includes(findingKey(c.finding)) })
  if (dropped.length) log('Round ' + round + ' — removed from the human-only ledger after re-verification: ' + dropped.map(function(c) { return findingKey(c.finding) }).join(', '))
  log('Round ' + round + ' — ' + confirmed.length + ' confirmed fixable defect(s), ' + openFindings.length + ' needing a human, across ' + outcome.dimensions.length + ' dimensions')

  if (confirmed.length === 0) break

  if (round >= MAX_ROUNDS) {
    openFindings = dedupeFindings(openFindings.concat(confirmed))
    log('Round cap (' + MAX_ROUNDS + ') reached with ' + confirmed.length + ' unresolved defect(s) — opening PR with caveats')
    break
  }

  impl = await reimplementTicket('A review round found the CONFIRMED findings below.\n\nCONFIRMED FINDINGS TO RESOLVE (round ' + round + '):\n' + confirmedFixes(confirmed), 're-implement r' + round)
  log('Re-implement (round ' + round + ') done')
}

var finalGate = await runGate('gate final #1', GATE_SCRIPT + ' --stamp-only')
if (gateVerdict(finalGate) !== 'green') throw new Error('Aborting before the push — the tree is not the one the last review round saw green:\n' + finalGate.output + '\nThe work stays in the worktree; nothing was pushed.')

phase('PR')

var pr = required(await agent([
  'CONTEXT — final implementation: ' + JSON.stringify(impl),
  'CONTEXT — the plan: ' + analysis.plan,
  'CONTEXT — assumptions the run made on ambiguities of the ticket (list them in the PR): ' + JSON.stringify(analysis.assumptions || []),
  'CONTEXT — unresolved defects after ' + round + ' review round(s) (fixableByAgent=false means the fix needs a human step; carry reason and fix into the caveats): ' + JSON.stringify(openFindings.map(ledgerEntry)),
  'CONTEXT — review findings the triage rejected or narrowed (carry each one, with its reason, into the caveats, so a human checks it): ' + JSON.stringify(dismissed.map(dismissalEntry)),
  '',
  'TASK — Open the pull request for ' + TICKET + ' by FOLLOWING the `open-pr` skill (sq-git plugin) as the SINGLE SOURCE OF TRUTH',
  'for the commit message + co-author line, branch push, filling .github/PULL_REQUEST_TEMPLATE.md by its per-section rules',
  '(removing empty sections), and creating a DRAFT PR. Invoke it with the Skill tool as `sq-git:open-pr`: that loads its steps',
  'and authorizes the gh call; do NOT reinvent them.',
  'Describe the code as it is now on disk: read git diff origin/' + BASE_BRANCH + '...HEAD and the working tree, not only the summaries above, which earlier passes wrote.',
  '',
  'Apply these workflow-specific inputs on top of the skill:',
  '- BEFORE creating a PR, check if one already exists: `gh pr view --json url 2>/dev/null`. If it does, update it instead of creating a new one.',
  '- Ticket: ' + TICKET + ' (use it as the [' + TICKET + '] title prefix and as the References / Issue Jira link).',
  '- Label: "' + PR_LABEL + '".',
  ...(ARGS.baseBranch ? ['- Base branch: ' + ARGS.baseBranch + '. This PR stacks on that branch: open it with the skill\'s `--base ' + ARGS.baseBranch + '` flag.'] : []),
  '- Stage the changes EXCLUDING CLAUDE.md, CLAUDE.local.md and the .claude/ directory. Never pass --no-verify: the pre-commit and pre-push hooks of the repo run the same checks the gate ran.',
  '- After committing and BEFORE any push, run `' + GATE_SCRIPT + ' --head --stamp-only`. It runs no check: it only confirms the committed tree is the one the gate stamped green and the review saw.',
  '  Push only when it exits 0. On any other exit do NOT push and do NOT open the PR: return created=false, an empty prUrl, and the gate output in notes.',
  '- If there are unresolved defects or findings the triage rejected or narrowed listed above, pass them via the skill\'s caveats (a clearly-marked "Known issues" / Caveats section).',
  '',
  'Return the PR url and the branch name.',
].join('\n'), { ...tier('deep'), phase: 'PR', label: 'open PR ' + TICKET, schema: PR_SCHEMA }), 'PR')

log('PR phase done — created: ' + (pr && pr.created) + '; url: ' + (pr && pr.prUrl))

return {
  ticket: TICKET,
  project: PROJECT,
  branch: branchInfo && branchInfo.branch,
  reviewRounds: round,
  implemented: impl,
  unresolvedFindings: openFindings.map(ledgerEntry),
  dismissedFindings: dismissed.map(dismissalEntry),
  pr: pr,
}
