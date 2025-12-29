# Digital ID MCP Server Setup Guide

This guide will help you set up the Digital ID MCP Server so you can use it with AI assistants like Claude Desktop or Cursor.

## Prerequisites

- Node.js 18+ installed
- Access to the Digital ID database
- Database credentials (host, database name, user, password)

## Quick Setup

1. **Navigate to the MCP server directory:**
```bash
cd mcp-server
```

2. **Install dependencies:**
```bash
npm install
```

3. **Create environment file:**
```bash
cp .env.example .env
```

4. **Edit `.env` with your database credentials:**
```env
DB_HOST=localhost
DB_NAME=digital_ids
DB_USER=your_db_user
DB_PASS=your_db_password
ORGANISATION_ID=1
```

**Required:** `ORGANISATION_ID` must be set. The MCP server only supports organisation-wide access for security. This ensures all queries are automatically filtered to the specified organisation, preventing access to other organisations' data.

5. **Build the server:**
```bash
npm run build
```

6. **Test the server:**
```bash
npm start
```

If you see "Digital ID MCP Server running on stdio", the server is working correctly. Press Ctrl+C to stop it.

## Configuring for Cursor

1. Open Cursor settings
2. Navigate to MCP settings (usually in Settings → Features → MCP)
3. Add the following configuration:

```json
{
  "mcpServers": {
    "digital-id": {
      "command": "node",
      "args": ["/absolute/path/to/digital-id/mcp-server/dist/index.js"],
      "env": {
        "DB_HOST": "localhost",
        "DB_NAME": "digital_ids",
        "DB_USER": "your_db_user",
        "DB_PASS": "your_db_password",
        "ORGANISATION_ID": "1"
      }
```

**Required:** `ORGANISATION_ID` must be set. The MCP server only supports organisation-wide access for security. This ensures the MCP server can only access data from the specified organisation, providing essential security for multi-tenant deployments.
    }
  }
}
```

**Important:** Replace `/absolute/path/to/digital-id` with the actual absolute path to your project directory.

4. Restart Cursor

## Configuring for Claude Desktop

1. Open Claude Desktop settings
2. Navigate to MCP settings
3. Add the server configuration similar to Cursor (see above)
4. Restart Claude Desktop

## Using the MCP Server

Once configured, you can use natural language to interact with your Digital ID system:

- "Get employee information for john.doe@example.com"
- "Show me verification logs for organisation ID 1 from last month"
- "List all employees with pending photo approvals"
- "Verify this ID card token: abc123..."
- "Revoke the ID card for employee ID 5"

## Troubleshooting

### Server won't start
- Check that Node.js is installed: `node --version`
- Verify database credentials in `.env`
- Ensure the database is accessible from your machine

### "Cannot find module" errors
- Run `npm install` again
- Ensure you're in the `mcp-server` directory
- Check that `dist/index.js` exists after building

### Database connection errors
- Verify database credentials
- Check that the database server is running
- Ensure network access to the database (if remote)

### Tools not appearing in AI assistant
- Restart your AI assistant application
- Check the MCP configuration syntax is valid JSON
- Verify the path to `dist/index.js` is correct and absolute

## Security Notes

⚠️ **Important Security Considerations:**

1. The MCP server has direct database access - protect your `.env` file
2. Never commit `.env` files to version control
3. Use read-only database users if possible for production
4. Consider implementing API authentication for production deployments
5. Monitor access logs regularly

## Next Steps

- Customize tools in `src/index.ts` to add your own functionality
- Add authentication/authorization if needed
- Implement rate limiting for production use
- Set up monitoring and logging



