# Phorever

Role-based long-running process daemon for PHP projects!

## Overview & Goals

Phorever provides an easy way to launch and manage background processes based on configuration defined in a `phorever.json` file.

Phorever will automatically keep track of all running processes, respawning them based on configured behavior, and fire notifications on aberrant behavior.

Phorever is installable via [Composer](http://getcomposer.org/) and automatically drops a binary to interact with.

Phorever's ultimate goal is to make provisioning servers and monitoring long-running processes more idiot proof. For example, a simple `phorever start scheduler` will ensure all background processes required for a scheduler server are running.

## Project Status

The project is currently in an MVP state after some exploratory coding. Currently it launches processes based on requested role and respawns processes after a resurrection wait time.

**Currently *not* supported, but desperately needed before BETA1:**

* ~~Redirecting logs~~
* Processes that daemonize themselves (cannot check custom PIDs)
* Giving up after certain thresholds
* Notification on aberrant behavior

**Features to come:**

* Configure respawn and notification behavior
* Hooks for your own custom event listener classes for more fine-grained control
* Status monitor and statistical logs