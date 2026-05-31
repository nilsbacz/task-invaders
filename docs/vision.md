# Vision

Task Invaders is a personal task board that turns recurring goals, routines, and responsibilities into a simple arcade-inspired system.

The core idea is: an invader is a task.

Tasks spawn onto board rows, move toward the base over time, and can be cleared by completing them. The board should make priorities visible without becoming a heavy planning tool. It should help answer one practical question: what should be handled next?

## Product Direction

Task Invaders should feel like a lightweight game layer on top of real tasks.

The app is for personal routines, shared household responsibilities, and recurring goals that are easy to ignore when they live in a plain checklist. Examples include workouts, running, cleaning, maintenance tasks, and project habits.

The system should stay simple enough to use casually. It should not become a full project management suite, calendar replacement, or productivity framework.

## Board Concept

A board contains rows. Rows limit how many parallel task lanes exist and help prevent overload.

Tasks belong to a row and can be moved to another row when needed. A board may have a row limit, with 20 rows as a possible upper bound.

Multiple tasks can exist on the same row. If a task has a shield, tasks behind it cannot be cleared first. This creates a natural priority rule: shielded tasks can block lower-priority work in the same row until they are completed.

Tasks without a shield do not block other tasks behind them. This allows optional or low-pressure tasks to exist without preventing more important work.

## Task Concept

A task describes what needs to be done and how it behaves on the board.

Important task properties include:

- Name
- Description
- Row
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

## Pixel Editor

The pixel editor is the spawn chamber.

Users should be able to create and edit their own sprites for tasks. Sprites should make tasks recognizable at a glance and support the arcade identity of the app.

The editor should stay focused and simple. It exists to make task invaders personal, not to become a full graphics tool.

## Presets

Presets should make it easy to create a useful board quickly.

A preset can define rows, task defaults, and example routines. Presets should support import and export so setups can be backed up, shared, or reused.

Users should eventually be able to create a preset from the current board setup.

## Language And Tone

The app should support at least two wording modes:

- casual: practical, calm task wording.
- Gamified: arcade-inspired task wording.

German translations may be supported in the future.

The underlying behavior should stay the same across wording modes. Only labels, descriptions, and user-facing language should change.

## Accounts And Sharing

The first version can focus on personal use.

Later, boards should be account-specific. Multiple users should be able to see or edit one shared board when permissions allow it. This is especially useful for household or shared responsibility boards.

Authentication should use standard Symfony features where possible.

## Design Direction

The interface should be readable, reusable, and simple.

The visual style should use the existing Task Invaders styleguide as the source of truth. The app should keep the square arcade-style language and avoid rounded corners.

Frontend work should support the actual board experience first: seeing tasks, understanding pressure, moving tasks, completing tasks, and managing presets.
