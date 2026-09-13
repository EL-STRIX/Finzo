## Summary
<!-- Concise description of what this PR changes and why. -->

## Related Issue
<!-- Link to the issue this PR addresses, e.g. Fixes #123 -->

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Refactor / code quality
- [ ] Documentation update
- [ ] Configuration / infrastructure change

## Testing
<!-- Describe how this was tested. Include commands if applicable. -->
- [ ] `vendor/bin/phpunit` passes locally with no regressions
- [ ] Manually verified in browser (list pages/flows tested)

## Contributor Checklist
- [ ] Code follows PSR-12 and project conventions (Clean Architecture, thin controllers, fat services)
- [ ] No dead code, debug statements, or commented-out blocks left behind
- [ ] All new business logic is covered by PHPUnit tests
- [ ] Database queries use PDO prepared statements; no raw SQL
- [ ] Input is validated via the central `Validator` service
- [ ] Commits are atomic and logically grouped with single-line messages
- [ ] Relevant `docs/` documentation updated if architecture changed
