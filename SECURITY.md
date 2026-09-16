# Security

These rules validate local structure. They do not authenticate a person, query a state registry, verify phone ownership, or authorize access.

Do not put real personal identifiers in public issue reports or test fixtures. For a security issue, contact the maintainer privately at jah6332@gmail.com with a synthetic reproduction.

## Dependencies and lifecycle

The declared compatibility targets are Laravel 10 and 11. Both are outside upstream security support as of September 2026. The compatibility test harness may therefore require a command-scoped Composer security-blocking exception. CI records the dependency audit separately; passing tests must not be interpreted as a clean security audit.

No Composer security policy override is distributed in this package's manifest. Maintain and audit the host application independently, and plan a migration to a supported framework version. Do not deploy the test harness as an application.
