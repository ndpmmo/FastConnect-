# FastConnect Plugin

## Introduction

FastConnect is a powerful plugin for PocketMine-MP, designed to optimize player connections and manage server performance. This plugin offers several useful features:

- Optimizes the connection process
- Manages player cache memory
- Automatically adjusts view distance
- Supports multiple languages (English and Vietnamese)

## Installation

1. Download the FastConnect plugin
2. Place the plugin file in your PocketMine-MP server's plugins folder
3. Restart the server

## Usage

### Basic Commands

Use the `/fastconnect` command with the following options:

- `enable`: Enable FastConnect
- `disable`: Disable FastConnect
- `status`: Check the current status of FastConnect
- `cache`: View cache information
- `cache clear`: Clear the cache
- `language`: View the current language
- `language <EN|VI>`: Change the language (English or Vietnamese)

## Main Features

1. **Connection Optimization**: FastConnect automatically processes login packets to improve player connection speed.

2. **Cache Management**: The plugin stores player information in cache memory for more efficient management.

3. **View Distance Adjustment**: FastConnect automatically adjusts view distance based on the number of online players to ensure optimal performance.

4. **Multi-language Support**: The plugin supports both English and Vietnamese, allowing users to easily switch between the two languages.

## Configuration

FastConnect automatically creates a `language.json` file in its plugin directory. You can edit this file to change notifications or add new languages.

## How it Works

1. When activated, the plugin creates the `language.json` file if it doesn't exist.

2. The plugin registers events to listen for login packets and manage player information.

3. Every minute, the plugin automatically adjusts view distance based on the number of online players.

4. Upon receiving commands, the plugin performs corresponding actions such as enabling/disabling, clearing cache, or changing language.

## Benefits

- Improves player connection speed
- Optimizes server performance
- Effectively manages player information
- Easy configuration and usage

FastConnect is a powerful tool that helps PocketMine-MP server administrators optimize performance and enhance player experience.
