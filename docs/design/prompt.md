You are working inside an existing codebase.

Your task is to PLAN the implementation and PRODUCE a Markdown document that contains:

1. A technical analysis of how to add webhook-based configuration management to the project.
2. A concrete, step-by-step implementation plan with follow-up actions.

## Feature to Design

Add support for managing system configuration via an HTTP webhook.

The webhook must:
- Accept incoming HTTP requests
- Parse, sanitize, and validate payloads
- Update or retrieve configuration based on the request
- Follow the architectural patterns and design conventions already present in the project

## Sources of Truth (read these first)

- Project architecture and patterns: @README.md  
- Expected webhook request/response formats: @docs/design/webhook.postman_collection.json  
- Existing webhook endpoint registration logic: commit 608f0eee0c1f5de12d08b8e26982a7942dff9ace  

You MUST examine these before proposing the design. Do not assume architecture that is not present in the repository.

## Architectural Constraints

- The solution must align with the project’s current structure and naming conventions
- Reuse existing infrastructure (routing, controllers, services, validation, config handling) wherever possible
- Do not introduce new frameworks or major abstractions unless clearly justified
- No new automated tests are required for this task

## Expected Design Direction

The initial concept is to introduce a `WebhookAPI` (or equivalently named component based on existing conventions) responsible for:

- Handling incoming webhook HTTP requests and routing them appropriately based on the topic value present in the payload
- Delegating validation and sanitization
- Calling the appropriate configuration services for read/write operations

You should evaluate whether this fits the current architecture and adjust if a different structural approach is more consistent with the codebase.

## Output Requirements (Markdown Document)

Produce a structured Markdown document with the following sections:

### 1. Current Architecture Summary  
Briefly summarize only the architectural elements relevant to adding a webhook endpoint (HTTP layer, routing, controllers, services, config storage).

### 2. Webhook Contract Analysis  
Derive the webhook behavior from the Postman collection:
- Endpoints
- HTTP methods
- Payload shapes
- Response formats
- Error cases that must be handled

### 3. Integration Strategy  
Explain how the webhook feature fits into the existing architecture:
- Where the endpoint should live
- Which existing layers should be reused
- Where new components are required

### 4. Component Design  
Describe each new or modified component:
- Responsibilities
- Key methods
- Data flow between layers
- Validation and error handling strategy

### 5. Data & Validation Rules  
Detail:
- Payload validation rules
- Sanitization requirements
- Mapping between webhook payload fields and internal configuration structures

### 6. Implementation Plan (Step-by-step)  
Provide an ordered list of implementation steps at the file/class level (e.g., “Create X class in Y folder”, “Extend Z router”, etc.)

### 7. Risks and Edge Cases  
List technical risks, ambiguity in the webhook spec, and architectural pitfalls to avoid.

### 8. Out of Scope  
Explicitly state that writing tests is out of scope for this task.

## Working Style

Work methodically. Base decisions only on what exists in the repository and the provided artifacts. Prefer consistency with existing patterns over inventing new ones.