# Sequential Thinking & Reasoning

## Problem Solving Framework

### 1. EXPLORE
- What files are involved?
- What is the current state?
- What patterns exist?

### 2. ANALYZE  
- Root cause identification
- Dependency mapping
- Risk assessment

### 3. PLAN
- Step-by-step approach
- Token budget allocation
- Agent assignment

### 4. EXECUTE
- CODER implements
- TESTER verifies
- REVIEWER validates

### 5. VERIFY
- Screenshot check
- Functional test
- User confirmation

---

## Decision Tree

```
Problem Reported
    │
    ├── UI Issue? → TESTER (screenshot) → CODER (fix) → TESTER (verify)
    │
    ├── DB Issue? → REVIEWER (analyze) → CODER (fix) → REVIEWER (verify)
    │
    ├── Logic Issue? → EXPLORER (find) → CODER (fix) → TESTER (test)
    │
    └── Security Issue? → REVIEWER (audit) → CODER (patch) → REVIEWER (verify)
```

---

## Reasoning Templates

### Bug Fix Template
```
OBSERVE: [What is broken]
HYPOTHESIZE: [Possible cause]
TEST: [Verify with screenshot/query]
FIX: [Minimal change]
VERIFY: [Confirm fix works]
```

### Feature Template
```
REQUIRE: [What user needs]
EXISTING: [What we have]
GAP: [What's missing]
IMPLEMENT: [How to add]
TEST: [Verify works]
```

### Refactor Template
```
CURRENT: [Present code]
PROBLEM: [Why needs change]
TARGET: [Desired state]
CHANGE: [Step by step]
TEST: [Ensure no breakage]
```

---

## Complex Problem Solving

### For Large Tasks
1. Break into subtasks
2. Assign to agents
3. Aggregate results
4. Test integration

### For Interconnected Issues
1. Find root cause
2. Draw dependency graph
3. Fix in order
4. Test each change

### For Performance Issues
1. Profile slow operations
2. Identify bottlenecks
3. Optimize critical path
4. Cache where possible

---

## Token Budget Strategy

### Per Task
- EXPLORE: 500 tokens max
- ANALYZE: 300 tokens max
- PLAN: 200 tokens max
- EXECUTE: 1000 tokens max
- VERIFY: 300 tokens max

### Total Budget: 2500 tokens per task

### If Exceeding
- Break into smaller tasks
- Use MCP tools instead
- Skip explanations
- Be concise

---

## Quality Checks

### Code Quality
- [ ] Follows AGENTS.md conventions
- [ ] Uses prepared statements
- [ ] No syntax errors
- [ ] Minimal changes

### UI Quality
- [ ] Screenshot taken
- [ ] All elements visible
- [ ] Responsive tested
- [ ] No overflow issues

### Database Quality
- [ ] Prepared statements used
- [ ] No SQL injection risk
- [ ] Indexes considered
- [ ] Backup before changes
