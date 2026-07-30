# Sub-Agents

Sub-agents are built recursively and attached as tools on the parent `DbAgent`:

```php
public function make(Agent $agent, array $visited = []): DbAgent
{
    if (in_array((string) $agent->getKey(), $visited, true)) {
        throw CircularAgentReferenceException::forAgent($agent);
    }

    $visited[] = (string) $agent->getKey();

    foreach ($agent->subAgents as $subAgent) {
        $tools[] = $this->make($subAgent, $visited);
    }
}
```

- Cycles are guarded twice, deliberately:
  - Write time: `assertNoCycles` in agent actions → friendly field-keyed 422
  - Runtime: `$visited` list in `make()` → `CircularAgentReferenceException`, protecting against data that bypassed actions (imports, direct DB writes, races) so execution can never infinite-loop
- Keep both guards when touching sub-agent wiring — neither replaces the other
- `$visited` is passed by value down each branch — diamond shapes (two parents sharing a sub-agent) are legal; only true cycles throw
