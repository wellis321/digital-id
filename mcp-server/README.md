# Digital ID MCP Server

An MCP (Model Context Protocol) server that provides tools and resources for interacting with the Digital ID application. This allows AI assistants and other applications to access employee data, verify ID cards, view verification logs, and perform administrative tasks.

## Features

### Available Tools

1. **get_employee** - Get employee information by ID, email, or employee reference
2. **verify_id_card** - Verify an ID card using QR code or NFC token
3. **get_verification_logs** - Get verification logs with optional filters
4. **list_employees** - List employees with optional filters
5. **get_organisation** - Get organisation information
6. **revoke_id_card** - Revoke an employee's ID card
7. **get_pending_photos** - Get list of employees with pending photo approvals

### Available Resources

- `digital-id://employees` - List of all employees
- `digital-id://organisations` - List of all organisations

## Installation

1. Install dependencies:
```bash
cd mcp-server
npm install
```

2. Build the TypeScript code:
```bash
npm run build
```

3. Create a `.env` file in the `mcp-server` directory:
```env
DB_HOST=localhost
DB_NAME=digital_ids
DB_USER=your_db_user
DB_PASS=your_db_password
ORGANISATION_ID=1
```

**Required:** `ORGANISATION_ID` must be set to specify which organisation's data the MCP server can access. The MCP server only supports organisation-wide access for security. All queries will be automatically filtered to this organisation only.

## Usage

### Running the Server

```bash
npm start
```

The server runs on stdio and communicates via JSON-RPC.

### Configuring in Cursor/Claude Desktop

Add this to your MCP settings (usually `~/.cursor/mcp.json` or similar):

```json
{
  "mcpServers": {
    "digital-id": {
      "command": "node",
      "args": ["/path/to/digital-id/mcp-server/dist/index.js"],
      "env": {
        "DB_HOST": "localhost",
        "DB_NAME": "digital_ids",
        "DB_USER": "your_db_user",
        "DB_PASS": "your_db_password",
        "ORGANISATION_ID": "1"
      }
```

**Required:** `ORGANISATION_ID` must be set. The MCP server only supports organisation-wide access for security. All queries will be automatically filtered to the specified organisation.
    }
  }
}
```

### Example Tool Calls

**Get employee by email:**
```json
{
  "name": "get_employee",
  "arguments": {
    "email": "john.doe@example.com"
  }
}
```

**Verify ID card:**
```json
{
  "name": "verify_id_card",
  "arguments": {
    "token": "abc123...",
    "verification_type": "qr"
  }
}
```

**Get verification logs:**
```json
{
  "name": "get_verification_logs",
  "arguments": {
    "organisation_id": 1,
    "start_date": "2025-01-01",
    "end_date": "2025-01-31",
    "limit": 50
  }
}
```

## Security Considerations

⚠️ **Important**: This MCP server provides direct database access. Ensure:

1. **Database credentials are secure** - Never commit `.env` files to version control
2. **Network security** - The server should only be accessible to trusted applications
3. **Access control** - Consider implementing authentication/authorization if exposing publicly
4. **Rate limiting** - Consider adding rate limiting for production use
5. **Audit logging** - All operations are logged in the verification_logs table

## Development

### Building

```bash
npm run build
```

### Watch Mode (Development)

```bash
npm run dev
```

### Adding New Tools

1. Add tool definition to `ListToolsRequestSchema` handler
2. Add tool implementation to `CallToolRequestSchema` handler
3. Rebuild: `npm run build`

## License

MIT



