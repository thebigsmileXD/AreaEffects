# AreaEffects
This plugin allows you to define areas in which players get effects when they move through (kind of similar to vanilla's lingering potions, just that these effect areas stay forever!)

Effect areas can be used for many things:
- Healing & regeneration areas in PvP
- Jump and speed boosters in Jump'n'Runs
- Fall damage removers (Using short jump potion or potion of slow falling)
- many more!

The plugin was initially coded by @Rspuds, but is now maintained by me for updates.

## Usage
Areas are set up ingame via commands.

To define an effect area, you use `/ae pos1` as the first corner and `/ae pos2` at the second corner.

After both corners are set, you can set which effect the are should give, and name it

`/ae create <areaname> <id> [amplifier] [duration] [visible]`

Example: `/ae create jumparea jump_boost 3 5 true`

The effect part is similar to the `/effect` command of Minecraft

`/ae list` Lists all areas

`/ae remove <name>` Removes an effect area by name
## Permissions
- "ae.commands.ae" - Default: OP; The permission to use the `/ae` command