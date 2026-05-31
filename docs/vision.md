# Vision

Task Invaders is a personal task board that turns recurring goals, routines, and responsibilities into a simple arcade-inspired system.

The core idea is: an invader is a task.

Tasks spawn into board lanes, move toward the base over time, and can be cleared by completing them. The board should make priorities visible without becoming a heavy planning tool. It should help answer one practical question: what should be handled next?

## Product Direction

Task Invaders should feel like a lightweight game layer on top of real tasks.

The app is for personal routines, shared household responsibilities, and recurring goals that are easy to ignore when they live in a plain checklist. Examples include workouts, running, cleaning, maintenance tasks, and project habits.

The system should stay simple enough to use casually. It should not become a full project management suite, calendar replacement, or productivity framework.

## Board And Lane Concept

A board contains lanes. Lanes limit how many parallel task streams exist and help prevent overload.

Tasks belong to a lane and can be moved to another lane when needed. A board may have a lane limit, with 20 lanes as a possible upper bound.

Multiple tasks can exist in the same lane. If a task has a shield, tasks behind it cannot be cleared first. This creates a natural priority rule: shielded tasks can block lower-priority work in the same lane until they are completed.

Tasks without a shield do not block other tasks behind them. This allows optional or low-pressure tasks to exist without preventing more important work.

The implementation may still use row-oriented names where they already exist, but the product language should prefer lane because it fits both casual task wording and the arcade metaphor.

## Board Playfield

The board detail page should become the active playfield, replacing the current form-heavy board structure view.

The playfield should make task pressure spatial. Each lane is shown as a vertical field. Tasks spawn near the top and move downward toward the base as time passes. A task's vertical position is calculated from the percentage of time elapsed between its spawn time and base time. The lower a task appears, the closer it is to needing attention.

The top of the board should stay compact: board breadcrumb, board title, a short expandable board description, and a lane add action. Board setup should remain available, but it should not dominate the normal board view.

Each lane should have a clear title and an add-task action. Upcoming tasks should be shown per lane above the active field with their respawn timing. Upcoming tasks must be hideable so the board can stay focused during daily use.

Active tasks should appear as invader tiles. A tile should communicate the task title or sprite, risk level, shield state, and remaining time without requiring the user to open details. Clicking a task opens its details and editing surface.

When multiple tasks occupy a similar position in the same lane, they should be displayed beside each other where possible. If there are too many to display cleanly, they may overlap in a controlled way and remain clickable, with a popup or detail widget showing the stacked tasks.

Disabled, finished, inactive, and non-respawning tasks should live on a separate task overview page. The active playfield should show what can currently affect the board.

## Task Concept

A task describes what needs to be done and how it behaves on the board.

Important task properties include:

- Name
- Description
- Lane
- First spawn time
- Spawn interval
- Time until it reaches the base
- Whether it can be completed again immediately
- Risk level
- Speed factor
- Shield state
- Sprite

Risk levels should communicate urgency or consequence. The current direction uses:

- Green: low pressure
- Yellow: medium pressure
- Red: high pressure

The exact wording can change depending on whether the app uses casual or gamified language.

## Spawn And Completion Behavior

Recurring tasks should spawn according to their configuration.

Time-based behavior should be testable. The app should eventually support a development-only way to fast-forward time so spawning, movement, base reaches, and recurring completion behavior can be tested without waiting in real time.

If a task spawns more often than it reaches the base, multiple instances of that task can exist at the same time. This makes overdue or repeated responsibilities visible instead of hiding them behind a single checklist item.

Completing or shooting a task should not erase its history. Completion should be recorded so the app can later show statistics, streaks, frequency, and long-term progress. Even one-time tasks should leave a durable completion record after they are cleared from the active board.

Some tasks should be immediately clearable again after completion. This is useful when doing the task early should create a buffer and move the next pressure point further into the future.

Other tasks should not be immediately clearable again. This is useful for habits where completing the task too often should not distort the intended rhythm.

The active board should only show what still needs attention. Historical completions can be hidden from the normal board view while still remaining available for stats and audit-style history.

Shooting a task always means completing the real task. The arcade language should never create a separate game-only action that can clear a task without the underlying work being done.

## Base Outcomes

What happens when a task reaches the base depends on its risk level.

Green tasks are low-pressure tasks. When a green task reaches the base, it should simply respawn according to its schedule. It should not interrupt the board.

Yellow tasks are medium-pressure tasks. When a yellow task reaches the base, it should be highlighted strongly. If it remains at the base for more than 10% of its own spawn-to-base lifetime, it should escalate into red behavior. This escalation belongs to the current active task instance. After the task is shot, future instances should behave as yellow again.

Red tasks are high-pressure tasks. When a red task reaches the base, the board view changes into a blocking focus state: only that task is displayed, and nothing else on the board is visible until the task is completed.

If multiple red-behaving tasks are waiting at the base, the blocking focus state should show the task that reached the base first. If two tasks reach the base at the exact same time, the task that was added most recently should be shown first. After the focused task is completed, the next waiting red-behaving task should appear immediately.

## Pixel Editor

The pixel editor is the spawn chamber.

Users should be able to create and edit their own sprites for tasks. Sprites should make tasks recognizable at a glance and support the arcade identity of the app.

The editor should stay focused and simple. It exists to make task invaders personal, not to become a full graphics tool.

## Presets

Presets should make it easy to create a useful board quickly.

A preset can define lanes, task defaults, and example routines. Presets should support import and export so setups can be backed up, shared, or reused.

Users should eventually be able to create a preset from the current board setup.

## Language And Tone

The app should support language as two separate choices:

- Tone: casual or gamified.
- Locale: English or German.

Casual tone should use practical task wording. Gamified tone should use arcade-inspired wording.

The underlying behavior should stay the same across tones and locales, but the board presentation may change slightly with the chosen tone. Casual mode can keep labels calmer and more task-oriented. Gamified mode can lean further into invaders, shooting, base pressure, and arcade status language.

## Accounts And Sharing

The first version can focus on personal use.

Later, boards should be account-specific. Multiple users should be able to see or edit one shared board when permissions allow it. This is especially useful for household or shared responsibility boards.

Authentication should use standard Symfony features where possible.

## Design Direction

The interface should be readable, reusable, and simple.

The visual style should use the existing Task Invaders styleguide as the source of truth. The app should keep the square arcade-style language and avoid rounded corners.

Frontend work should support the actual board experience first: seeing tasks, understanding pressure, moving tasks, completing tasks, and managing presets.
