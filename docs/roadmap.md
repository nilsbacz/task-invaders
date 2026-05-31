# Roadmap

This is a lightweight checklist for personal planning. No timelines required.

## Basic Frontend Design

- [x] Style/script assets
- [x] First stylesheet
- [ ] Breadcrumbs
- [ ] Lane structure setup view

## Preset Editor

- [x] General YAML structure
- [x] Default lanes
- [x] First preset
- [x] Default tasks
- [ ] More presets, selectable
- [ ] Make preset from current setup
- [ ] Preset editor: import and export function

## Boards

- [ ] Make it pretty
- [ ] Make it account specific
- [ ] Selectable presets
- [ ] Replace board detail page with the playfield view
- [ ] Keep setup/editing available without dominating the board page

## Board Functionality

- [x] Add/edit/remove lanes
- [x] Add/edit/remove tasks
- [ ] Move tasks between lanes
- [ ] Spawn active task instances from task schedules
- [x] Shooting tasks
- [ ] Auto shooting with vacation mode

## Game Mechanics

- [ ] Define task instance model
- [ ] Calculate invader position from spawn time and base time
- [ ] Enforce shield blocking rules
- [ ] Define green base behavior: respawn without interrupting the board
- [ ] Define yellow base behavior: strong highlight at the base
- [ ] Escalate yellow tasks to red behavior after 10% extra lifetime at the base
- [ ] Reset escalated yellow tasks back to yellow behavior after completion
- [ ] Define red base behavior: blocking focus state until completion
- [ ] Implement red focus order: earliest base reach first, exact tie newest task first
- [ ] Immediately show the next waiting red task after the focused task is completed
- [ ] Track completed shots/task completions
- [ ] Preserve completion history instead of deleting completed tasks

## Stats

- [ ] Track how often each task is completed
- [ ] Track completion history over time
- [ ] Add basic stats board
- [ ] Show task completion frequency
- [ ] Show long-term progress per board

## Pixel Editor

- [ ] Build basic sprite editor
- [ ] Save sprites
- [ ] Assign sprites to tasks
- [ ] Show task sprites on the board

## Task Details

- [ ] Add task descriptions
- [ ] Support checklist-style task instructions
- [ ] Show risk level visually
- [ ] Show shield state visually
- [ ] Show next spawn/base timing
- [ ] Open task details/editing from a playfield task tile

## Board Experience

- [ ] Render tasks as invaders
- [ ] Render lanes as vertical playfield columns
- [ ] Show base/progress per lane
- [ ] Position tasks by elapsed spawn-to-base percentage
- [ ] Show blocked tasks behind shielded tasks
- [ ] Add compact board header with breadcrumb, title, expandable description, and add-lane action
- [ ] Show upcoming tasks per lane
- [ ] Make upcoming task sections hideable per lane
- [ ] Render risk level through task tile borders
- [ ] Render shield state through an additional tile marker or border
- [ ] Handle overlapping tasks in the same lane
- [ ] Add popup/detail widget for overlapped task stacks
- [ ] Add red-task focus state that hides the rest of the board
- [ ] Add empty states for lanes and boards
- [ ] Add confirmation for destructive actions

## Task Overview

- [ ] Add separate task overview page
- [ ] Show disabled tasks
- [ ] Show finished tasks
- [ ] Show inactive or non-respawning tasks
- [ ] Link from the playfield to task overview

## Wording And Language

- [ ] Implement casual English wording
- [ ] Implement gamified English wording
- [ ] Implement casual German wording
- [ ] Implement gamified German wording
- [ ] Add tone switch: casual/gamified
- [ ] Add locale switch: English/German
- [ ] Allow small board presentation differences between casual and gamified tone

## Accounts

- [ ] Ensure standard Symfony login
- [ ] Rights system: multiple users can see/edit one board

## Deployment

- [ ] First prototype hosted on Raspberry Pi
- [ ] Viewable on `https://nilsbaczynski.de/`
- [ ] Figure out deployment

## Developer Experience

- [ ] Add fixture command for local demo data
- [ ] Add development-only fast-forward time feature
- [ ] Document local development commands
- [ ] Add CI status notes to README
- [ ] Add roadmap/vision links to README

## Data Safety

- [ ] Add backup/export for boards
- [ ] Add import for boards
- [ ] Add delete confirmation flows

## Tutorial

- [ ] Plan how the system will work
- [ ] Plan what to explain
- [ ] Write explanation texts
