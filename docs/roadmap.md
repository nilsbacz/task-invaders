# Roadmap

This is a lightweight checklist for personal planning. No timelines required.

## Basic Frontend Design

- [x] Style/script assets
- [x] First stylesheet
- [ ] Breadcrumbs
- [ ] Board structure

## Preset Editor

- [x] General YAML structure
- [x] Default rows
- [x] First preset
- [x] Default tasks
- [ ] More presets, selectable
- [ ] Make preset from current setup
- [ ] Preset editor: import and export function

## Boards

- [ ] Make it pretty
- [ ] Make it account specific
- [ ] Selectable presets

## Board Functionality

- [ ] Add/edit/remove rows
- [ ] Add/edit/remove tasks
- [ ] Moving and spawning tasks
- [ ] Shooting tasks
- [ ] Auto shooting with vacation mode

## Game Mechanics

- [ ] Define task instance model
- [ ] Calculate invader position from spawn time and base time
- [ ] Enforce shield blocking rules
- [ ] Define what happens when a task reaches the base
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

## Board Experience

- [ ] Render tasks as invaders
- [ ] Show base/progress per row
- [ ] Show blocked tasks behind shielded tasks
- [ ] Add empty states for rows and boards
- [ ] Add confirmation for destructive actions

## Wording And Language

- [ ] Implement first wording convention: professional
- [ ] Implement German translations
- [ ] Implement second wording convention with a switch system: gamified

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
