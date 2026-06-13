# SPK Bansos MCP Server

A Model Context Protocol (MCP) server for the SPK Bansos application with tools for MySQL database access and Test-Driven Development (TDD).

## Installation

The MCP server is installed globally and can be started with:

```bash
mcp-server-bansos
```

## Available Tools

### 1. **mysql_query** - Database Access
Execute SQL queries against the `spk_bansos` XAMPP MySQL database.

**Parameters:**
- `query` (required): SQL query to execute
- `params` (optional): Array of parameters for prepared statements

**Example:**
```json
{
  "query": "SELECT * FROM users WHERE id = ?",
  "params": [1]
}
```

**Response:**
```json
{
  "success": true,
  "rows": [...],
  "affectedRows": 0
}
```

### 2. **tdd_command** - Test-Driven Development
Execute test commands for the Laravel application.

**Parameters:**
- `action` (required): One of:
  - `run_all_tests` - Run all tests
  - `run_unit_tests` - Run only unit tests
  - `run_feature_tests` - Run only feature tests
  - `run_specific_test` - Run a specific test file
  - `watch_tests` - Watch tests and re-run on changes
  - `coverage` - Run tests with code coverage
- `testFile` (optional): Path to specific test file (for `run_specific_test`)
- `filter` (optional): Filter tests by method name or pattern

**Example:**
```json
{
  "action": "run_specific_test",
  "testFile": "tests/Feature/ExampleTest.php",
  "filter": "testExample"
}
```

## Configuration

### Claude Desktop Configuration

Add to `~/.claude_desktop_config.json`:

```json
{
  "mcpServers": {
    "spk-bansos": {
      "command": "mcp-server-bansos"
    }
  }
}
```

## Build & Development

```bash
cd mcp

# Install dependencies
npm install

# Build TypeScript
npm run build

# Start development server
npm run dev

# Start production server
npm start
```

## Project Structure

```
mcp/
├── src/
│   ├── index.ts          # Main server file
│   └── tools/
│       ├── mysql.ts      # MySQL tool implementation
│       └── tdd.ts        # TDD tool implementation
├── dist/                 # Compiled JavaScript
├── package.json
└── tsconfig.json
```

## Database Configuration

The server is configured to connect to:
- **Host:** localhost
- **User:** root
- **Password:** (empty)
- **Database:** spk_bansos
- **Port:** 3306

These settings can be modified in `src/tools/mysql.ts` if needed.

## Features

- ✅ Execute SQL queries with prepared statement support
- ✅ Run Laravel tests (all, unit, feature, or specific)
- ✅ Watch mode for test development
- ✅ Code coverage analysis
- ✅ Test filtering by method name or pattern
- ✅ Error handling and detailed responses
