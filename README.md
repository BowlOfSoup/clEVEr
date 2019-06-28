
# clEVEr - EVE Online portal and Discord bot

Based on PHP ^7.1 (Symfony 4.3.*) and MySQL.

### Support
Join the [Discord channel](https://discord.gg/6h5xNUP).


### What is it for?

With this web-portal you can make your corp. or alliance members authenticate.

* Discord roles can be added by the members themselves (via auth command)
* Discord roles are automatically removed if members leave (alli/corp)
* Have a corporation bulletin that members can read
* Authorize certain members to edit the corporation bulletin

Future expansions:

* Inspection tool and HR module; Certain members can inspect others (for recruitment)
* As member, an overview of your killboard, industry jobs, mail, mining ledger, PI jobs, skill queue and wallet
* Feeds and notification in Discord of what is going on with your members/corp.

### Screenshots

_Overview of landing page after login_

![](http://i67.tinypic.com/1761k6.png "screenshot")

_Discord authentication request_

![](http://i67.tinypic.com/14wvtjn.png "screenshot")

_Authenticating in Discord_

![](http://i67.tinypic.com/2afenf7.png "screenshot")

_Automatic role removal if applicable_

![](http://i65.tinypic.com/2vb7why.png "screenshot")

_Editing the corporation bulletin_

![](http://i65.tinypic.com/30wrvpw.png "screenshot")

---

### How to install
#### 1. Setup your EVE Online application
To have your users login to the clEVEr portal you need to create an EVE application [here](https://developers.eveonline.com/applications). Log in with you EVE account, and click on 'applications' → 'create new application'. 

| Field | Value |
|---|---|
| Name | fill for example: `clEVEr portal` |
| Description | E.g. `A web portal for my corporation` |
| Connection Type | Choose `Authentication & API Access` |
| Permissions | Choose the permissions in the list blow |
| Callback URL | Must be: https://your-url-for-this.portal/**callback** |

**Permissions**

* `publicData`
* `esi-calendar.read_calendar_events.v1`
* `esi-skills.read_skillqueue.v1`
* `esi-wallet.read_character_wallet.v1`
* `esi-killmails.read_killmails.v1`
* `esi-planets.manage_planets.v1`
* `esi-mail.organize_mail.v1`
* `esi-mail.read_mail.v1`
* `esi-industry.read_character_jobs.v1`
* `esi-industry.read_character_mining.v1`
* `esi-industry.read_corporation_mining.v1`
* `esi-characters.read_corporation_roles.v1`
* `esi-killmails.read_corporation_killmails.v1`
* `esi-corporations.read_contacts.v1`
* `esi-corporations.read_corporation_membership.v1`
* `esi-corporations.read_starbases.v1`
* `esi-corporations.read_structures.v1`
* `esi-corporations.read_facilities.v1`

You'll need the 'Client ID' and the 'Secret Key' to be configured in the clEVEr application.

#### 2. Setup your Discord bot
To have your Discord working together with the clEVEr portal, create a Discord application [here](https://discordapp.com/developers/applications/). Log in and click 'New Application'. Give the bot the name `clEVEr` or any other name you want. Click 'Create'.

* Choose an icon. This will be the icon of your Discord application.

Go to 'Bot' (in the left-side menu).

* Choose an icon. This will be the icon/avatar of your **bot** in your Discord channel(s).
* Choose a username. This will be the name other members of your Discord will see.

Go to 'OAuth2' (in the left-side menu).

* For 'scopes' click `bot`.
* For permissions click:
	* `Manage Roles`
	* `Kick Members`
	* `Change Nickname`
	* `Manage Nicknames`
	* `View Channels`
	* `Send Messages`
	* `Send TTS Messages`
	* `Manage Messages`
	* `Read Message History`
	* `Mention Everyone`

Now click the 'copy' button in the middle of the page. Open the link in your browser (addressbar) to invite the bot in your Discord.

#### 3. Install clEVEr on a server
You can `git clone` this repository and make a vhost on your machine that points to the `/public` directory of the installation. **If you need help, contact me** on Discord (see above).

When cloned, copy the dummy configuration files:

	cp configuration.dist.json configuration.local.json
	cp .env .env.local
	
**Edit the configuration values!** See below.
	
Now install:

	composer install --no-dev
	
Note: By default this Symfony installation is set to production mode, if you want to contribute, make sure your `APP_ENV` in `.env.local` is set to `dev`.

**! important**. If you make changes to the configuration files, you have to run `composer install --no-dev` again. 

### How to use, server side
There are 'commands' available to run the Discord bot subscribers.

#### Subscriber
Command to have a long-running-process to listen to commands from the Discord channels the bot has read-rights for. You can start this manually (with an always-open terminal screen or [tmux](https://github.com/tmux/tmux/wiki)) or with manage it with [supervisord](http://supervisord.org/introduction.html).

	bin/console clever:discord:consumer

#### Role validator
Command to have a process that validates the roles Discord users have agains the configuration (and removes/adds them if applicable). You can put this in a **cronjob**.

	bin/console clever:discord:police

### Configuration reference

If you make changes to the configuration files, you have to run composer install --no-dev again.

#### .env.local
Environment variables.

| Key | Description | Example |
|---|---|---|
| `APP_ENV` | Environment setting | Can be `prod` or `dev` |
| `APP_SECRET` | String used for user authentication | anything 'secret' |
| `DATABASE_URL` | Database connection string | `mysql://user:pass@127.0.0.1:3306/databasename` |
| `EVE_APP_CALLBACK_URL` | The callback url you chose in the EVE application | https://your.url/**callback** |
| `EVE_APP_CLIENT_ID` | Your EVE application ID | `ca2482w8508b431839c228bcs04ds4e4` |
| `EVE_APP_CLIENT_SECRET` | Your EVE application secret | `0r8vT89cYk9zofBBQfU87LtrxHiw` |
| `DISCORD_BOT_TOKEN` | The token for you Discord bot (found in your Discord application under 'General Information') | `NTkxMDs8` |

#### configuration.local.json
Application configuration. 

* Get EVE char/alli/corp IDs [here](https://evewho.com/) search, click, then look for the ID behind the 'Last Updated' text (small letters at the bottom).
* [How-to](https://discordia.me/developer-mode) get Discord IDs

##### `powered_by`

If the `name` is `null`, the 'Powered by block will not be displayed'.

| Key | Description |
|---|---|
| `alliance_id` | An alliance id (optional, default `null`) |
| `corporation_id` | A corporation id (optional, default `null`), not used if the `alliance_id` is filled |
| `name` | Can be alli/corp name |

##### `corporation_bulletins`
An array of corporations that have a bulletin, and the EVE character IDs that can edit the bulletin.

	 "corporation_bulletins": {
      "CORPORATION ID 1": [		<== this corporation has a bulletin
        "CHARACTER ID 1",		<== these EVE character IDs can edit the bulletin
        "CHARACTER ID 2"
      ]
    },

##### `authorized_roles`
An array of roles that need authorization. List **all** the roles here that you use in `alliance_roles` or `corporation_roles`. Example:

	"authorized_roles": [
      28312351323,
      21340591324,
      29123495323
    ],

##### `alliance_roles` and `corporation_roles`
Per alliance or corporation, indicate which roles must be added to the Discord members.

##### `bot_log_channel`
The Discord channel ID the bot will output some logging to (e.g. the 'police' log).

-- Again. If you need help, contact me on Discord (see above).

### Contributing
Please! Make forks, pull requests etc. And let me know on Discord :)
