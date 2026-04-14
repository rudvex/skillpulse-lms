---
name: gsd:set-profile
description: Switch model profile for GSD agents (quality/balanced/budget/inherit)
argument-hint: <profile (quality|balanced|budget|inherit)>
model: haiku
allowed-tools:
  - Bash
---

Show the following output to the user verbatim, with no extra commentary:

!`node "/Users/hardip/Workspace/devilbox/data/www/sp-lms/htdocs/wp-content/plugins/skillpulse-lms/.claude/get-shit-done/bin/gsd-tools.cjs" config-set-model-profile $ARGUMENTS --raw`
