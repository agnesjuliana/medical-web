<claude-mem-context>
# Memory Context

# [medical-web] recent context, 2026-04-20 8:06pm GMT+7

Legend: 🎯session 🔴bugfix 🟣feature 🔄refactor ✅change 🔵discovery ⚖️decision
Format: ID TIME TYPE TITLE
Fetch details: get_observations([IDs]) | Search: mem-search skill

Stats: 21 obs (8,814t read) | 270,961t work | 97% savings

### Apr 19, 2026
215 7:07p 🔵 PR #21 commit 2719f866 targets three files with code review fixes
216 " 🟣 Commit 2719f866 migrated database.php from MySQL hardcoded credentials to PostgreSQL URL parsing
217 " 🔴 OnboardingPage.tsx imperial unit bug fixed by extracting parseHeightCm and parseWeightKg helpers
218 " 🔄 OnboardingPage.tsx types and STEPS config extracted to onboarding-config.tsx; dead MacroRing and ResultsContent components removed
219 7:08p 🔵 urldecode() is wrong fix for PostgreSQL URL passwords — rawurldecode() is the correct function
220 " 🔵 MotivationContent still hardcodes "kg" label for imperial users after the unit fix
221 " 🔵 Frontend app stack confirmed: React 19 + Vite 8 + TypeScript 6 + Tailwind 4 + shadcn/radix-ui
222 " 🔵 TypeScript build fails with 16 pre-existing TS6133/TS1484 errors across unrelated files
223 " 🟣 PR #21 review comment posted flagging urldecode() bug in config/database.php
224 7:09p 🔵 PR review comment successfully posted; PR now has 3 review comments with no merge-blocking decision
225 7:11p 🟣 GitHub Issue Delegation Workflow for Module 8 Task 2
226 " 🔵 Module 8 API Implementation Plan — Full Task Breakdown
227 " 🟣 issue.md Created for Task 2 GitHub Issue Delegation
228 7:12p 🟣 GitHub Issue #25 Created for Task 2 — User Profile & Dashboard Endpoints
239 8:55p 🟣 Automated PR Code Review Task Initiated for medical-web Repository
240 8:56p 🔵 PR #26 vs PR #21 Mismatch Confirmed — Commit Belongs to PR #26
241 " 🟣 modul_8 api.php: get_profile, save_profile, get_dashboard Endpoints Implemented
242 " 🔵 4 Code Review Issues Found and Posted to PR #26 for Commit cb816ac4
249 9:00p 🔴 All 4 PR #26 Code Review Issues Fixed in Commit 5e8c5c72
250 9:01p 🔵 PHP 8.x getLastErrors() Returns false Instead of Array When No Errors Exist
251 " 🔵 Second Round Review on PR #26 Found 2 Residual Bugs in Fix Commit 5e8c5c72

Access 271k tokens of past work via get_observations([IDs]) or mem-search skill.
</claude-mem-context>

<!-- code-review-graph MCP tools -->
## MCP Tools: code-review-graph

**IMPORTANT: This project has a knowledge graph. ALWAYS use the
code-review-graph MCP tools BEFORE using Grep/Glob/Read to explore
the codebase.** The graph is faster, cheaper (fewer tokens), and gives
you structural context (callers, dependents, test coverage) that file
scanning cannot.

### When to use graph tools FIRST

- **Exploring code**: `semantic_search_nodes` or `query_graph` instead of Grep
- **Understanding impact**: `get_impact_radius` instead of manually tracing imports
- **Code review**: `detect_changes` + `get_review_context` instead of reading entire files
- **Finding relationships**: `query_graph` with callers_of/callees_of/imports_of/tests_for
- **Architecture questions**: `get_architecture_overview` + `list_communities`

Fall back to Grep/Glob/Read **only** when the graph doesn't cover what you need.

### Key Tools

| Tool | Use when |
|------|----------|
| `detect_changes` | Reviewing code changes — gives risk-scored analysis |
| `get_review_context` | Need source snippets for review — token-efficient |
| `get_impact_radius` | Understanding blast radius of a change |
| `get_affected_flows` | Finding which execution paths are impacted |
| `query_graph` | Tracing callers, callees, imports, tests, dependencies |
| `semantic_search_nodes` | Finding functions/classes by name or keyword |
| `get_architecture_overview` | Understanding high-level codebase structure |
| `refactor_tool` | Planning renames, finding dead code |

### Workflow

1. The graph auto-updates on file changes (via hooks).
2. Use `detect_changes` for code review.
3. Use `get_affected_flows` to understand impact.
4. Use `query_graph` pattern="tests_for" to check coverage.
