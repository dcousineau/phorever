# Phorever

Role-based long-running process daemon for PHP projects!

## Overview & Goals

Phorever provides an easy way to launch and manage background processes based on configuration defined in a `phorever.json` file in your project's root directory.

Phorever will automatically keep track of all running processes, respawning them based on configured behavior, and fire notifications on aberrant behavior.

Phorever is installable via [Composer](http://getcomposer.org/) and automatically drops a binary to interact with.

Phorever's ultimate goal is to make provisioning servers and monitoring long-running processes more idiot proof. For example, a simple `phorever start scheduler` will ensure all background processes required for a scheduler server are running.

## Installation

Phorever is provided as a [composer package](http://getcomposer.org/) and requires PHP 5.4 and up. To use Phorever, simply add:

```json
{
    "require": {
        "dcousineau/phorever": "dev-master"
    }
}
```

Composer will attempt to symlink the Phorever executable into your own bin folder. It is advisable that you make sure your own `composer.json` file is updated to define where that bin directory is located:

```json
{
    "config": {
        "bin-dir": "bin"
    }
}
```

## Configuration

Phorever by default reads from a configuration file found in your project root named `phorever.json`.

This configuration file defines the individual processes that should be launched and tracked, what roles they belong to, where log files should be dumped, among other things.

An example configuration file would look like:

```json
{
    "pidfile": "./phorever_cool.pid",
    "timezone": "America/Chicago",
    "logging": {
        "directory": "./logs/"
    },
    "processes": [
        {
            "name": "Long Lived",
            "roles": ["roleb"],

            "up": "./tests/commands/longlived",

            "log_forwarding": true
        },
        {
            "name": "Short Lived",
            "roles": ["rolea"],

            "up": "./tests/commands/shortlived",

            "resurrect_after": 10,

            "clones": 2,

            "log_forwarding": true
        },
        {
            "name": "Runaway",
            "roles": ["runaway"],

            "up": "./tests/commands/runaway",

            "resurrect_after": 1,

            "log_forwarding": true
        }
    ]
}
```

Running the command `bin/phorever start rolea roleb` will launch both the "Short Lived" and "Long Lived" processes given they both belong to at least one of the roles we've requested to start.

Passing the `--daemon` parameter to Phorever will cause it to fork into the background.

## Project Status

The project is currently in an MVP state after some exploratory coding. Currently it launches processes based on requested role and respawns processes after a resurrection wait time.

**Currently *not* supported, but desperately needed before BETA1:**

* Processes that daemonize themselves (cannot check custom PIDs)
* Giving up after certain thresholds
* Notification on aberrant behavior

**Features to come:**

* Configure respawn and notification behavior
* Hooks for your own custom event listener classes for more fine-grained control
* Status monitor and statistical logs
