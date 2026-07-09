<?php
require_once dirname(__DIR__) . '/config/config.php';

$pageTitle = 'AI Integration (MCP) - Documentation';
include INCLUDES_PATH . '/header.php';
?>

<link rel="stylesheet" href="<?php echo url('assets/css/docs.css'); ?>?v=<?php echo filemtime(dirname(__DIR__) . '/public/assets/css/docs.css'); ?>">

<div class="docs-container">
    <?php include INCLUDES_PATH . '/docs-sidebar.php'; ?>

    <main class="docs-content">
            <h1>AI Integration (MCP Server)</h1>
            <p>Digital ID includes a Model Context Protocol (MCP) server that allows AI assistants like Cursor or Claude Desktop to interact with your Digital ID system directly.</p>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Organisation-Wide Access Only</h4>
                <p><strong>The MCP server only supports organisation-wide access for security.</strong> You must configure <code>ORGANISATION_ID</code> in your environment, and the server will automatically restrict all queries to that organisation only.</p>
                <p style="margin-top: 0.75rem;">This ensures:</p>
                <ul style="margin-top: 0.5rem;">
                    <li>Each MCP server instance can only access one organisation's data</li>
                    <li>No risk of cross-organisation data access</li>
                    <li>Safe for multi-tenant deployments</li>
                    <li>Organisation-level security by design</li>
                </ul>
                <p style="margin-top: 0.75rem;"><strong>Access Control:</strong> The MCP server requires database credentials and a configured <code>ORGANISATION_ID</code>. Once configured, it can only access data from the specified organisation. This makes it suitable for trusted administrators who need to query their organisation's data.</p>
            </div>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> What is MCP?</h4>
                <p>The Model Context Protocol (MCP) is a standard protocol that allows AI assistants to access external data and perform actions through secure, standardised interfaces. The Digital ID MCP server provides AI assistants with tools to query employee data, verify ID cards, view verification logs, and perform administrative tasks.</p>
            </div>
            
            <h2>Overview</h2>
            <p>The MCP server acts as a bridge between AI assistants and your Digital ID database, allowing natural language queries and automated tasks:</p>
            <ul>
                <li><strong>Employee Lookups:</strong> Find employee information by ID, email, or reference number</li>
                <li><strong>ID Card Verification:</strong> Verify ID cards using QR codes or NFC tokens</li>
                <li><strong>Log Analysis:</strong> Query verification logs with filtering options</li>
                <li><strong>Employee Management:</strong> List employees, view pending photo approvals, and manage ID cards</li>
                <li><strong>Organisation Data:</strong> Access organisation information and structure</li>
            </ul>
            
            <h2>How It Works</h2>
            <p>The MCP server is a TypeScript/Node.js application that:</p>
            <ol class="step-list">
                <li>
                    <strong>Connects to Your Database:</strong> Uses MySQL connection to access Digital ID data securely
                </li>
                <li>
                    <strong>Exposes Tools:</strong> Provides standardised tools that AI assistants can call (like functions)
                </li>
                <li>
                    <strong>Communicates via JSON-RPC:</strong> Uses the Model Context Protocol standard over stdio (standard input/output)
                </li>
                <li>
                    <strong>Returns Structured Data:</strong> Formats database results as JSON for AI assistants to process
                </li>
            </ol>
            
            <h2>Available Tools</h2>
            <p>The MCP server provides the following tools that AI assistants can use:</p>
            
            <h3>Employee Management</h3>
            <table>
                <thead>
                    <tr>
                        <th>Tool</th>
                        <th>Description</th>
                        <th>Parameters</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>get_employee</code></td>
                        <td>Get employee information</td>
                        <td>employee_id, email, employee_reference, organisation_id</td>
                    </tr>
                    <tr>
                        <td><code>list_employees</code></td>
                        <td>List employees with filters</td>
                        <td>organisation_id, is_active, has_photo, limit</td>
                    </tr>
                    <tr>
                        <td><code>get_pending_photos</code></td>
                        <td>Get employees with pending photo approvals</td>
                        <td>organisation_id</td>
                    </tr>
                </tbody>
            </table>
            
            <h3>Verification</h3>
            <table>
                <thead>
                    <tr>
                        <th>Tool</th>
                        <th>Description</th>
                        <th>Parameters</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>verify_id_card</code></td>
                        <td>Verify an ID card token</td>
                        <td>token, verification_type (qr/nfc/ble)</td>
                    </tr>
                    <tr>
                        <td><code>get_verification_logs</code></td>
                        <td>Get verification logs with filters</td>
                        <td>employee_id, organisation_id, verification_type, result, start_date, end_date, limit</td>
                    </tr>
                    <tr>
                        <td><code>revoke_id_card</code></td>
                        <td>Revoke an employee's ID card</td>
                        <td>employee_id, reason</td>
                    </tr>
                </tbody>
            </table>
            
            <h3>Organisation</h3>
            <table>
                <thead>
                    <tr>
                        <th>Tool</th>
                        <th>Description</th>
                        <th>Parameters</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>get_organisation</code></td>
                        <td>Get organisation information</td>
                        <td>organisation_id, domain</td>
                    </tr>
                </tbody>
            </table>
            
            <h2>Available Resources</h2>
            <p>In addition to tools, the MCP server provides resources that AI assistants can read:</p>
            <ul>
                <li><strong><code>digital-id://employees</code>:</strong> List of all employees (limited to 1000 records)</li>
                <li><strong><code>digital-id://organisations</code>:</strong> List of all organisations</li>
            </ul>
            
            <h2>Setting Up the MCP Server</h2>
            <p>To use the MCP server with an AI assistant like Cursor or Claude Desktop, follow these steps:</p>
            
            <h3>Prerequisites</h3>
            <ul>
                <li>Node.js 18+ installed on your system</li>
                <li>Access to the Digital ID database</li>
                <li>Database credentials (host, database name, username, password)</li>
                <li>An AI assistant that supports MCP (Cursor, Claude Desktop, etc.)</li>
            </ul>
            
            <h3>Step 1: Install Dependencies</h3>
            <ol class="step-list">
                <li>Navigate to the MCP server directory:
                    <pre><code>cd mcp-server</code></pre>
                </li>
                <li>Install Node.js dependencies:
                    <pre><code>npm install</code></pre>
                </li>
            </ol>
            
            <h3>Step 2: Configure Environment</h3>
            <ol class="step-list">
                <li>Create a <code>.env</code> file in the <code>mcp-server</code> directory</li>
                <li>Add your database credentials and organisation ID:
                    <pre><code>DB_HOST=localhost
DB_NAME=digital_ids
DB_USER=your_db_user
DB_PASS=your_db_password
ORGANISATION_ID=1</code></pre>
                </li>
                <li>Replace the values with your actual database credentials</li>
                <li><strong>Required:</strong> Set <code>ORGANISATION_ID</code> to the ID of the organisation whose data you want to access. The MCP server only supports organisation-wide access for security - all queries will be automatically filtered to this organisation.</li>
            </ol>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Organisation-Wide Access Required</h4>
                <p>The MCP server <strong>requires</strong> <code>ORGANISATION_ID</code> to be set in your environment configuration. This ensures the server can only access data from that specific organisation, providing essential security for multi-tenant deployments.</p>
                <p style="margin-top: 0.75rem;">When <code>ORGANISATION_ID</code> is configured:</p>
                <ul style="margin-top: 0.5rem;">
                    <li>All employee queries are automatically filtered to the specified organisation</li>
                    <li>Verification logs only show data from that organisation</li>
                    <li>Only employees from that organisation can be revoked</li>
                    <li>Resources (employees, organisations) only show data from that organisation</li>
                    <li>The server will fail to start if <code>ORGANISATION_ID</code> is not set</li>
                </ul>
                <p style="margin-top: 0.75rem;"><strong>Example:</strong> If you set <code>ORGANISATION_ID=5</code>, the MCP server will only be able to access data from organisation ID 5, even if the database contains data from multiple organisations.</p>
                <p style="margin-top: 0.75rem;"><strong>Multi-Organisation Deployments:</strong> If you need to access data from multiple organisations, you must set up separate MCP server instances, each with its own <code>ORGANISATION_ID</code> configuration.</p>
            </div>
            
            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> Security Warning</h4>
                <p>Never commit the <code>.env</code> file to version control. It contains sensitive database credentials. The <code>.gitignore</code> file is already configured to exclude it.</p>
            </div>
            
            <h3>Step 3: Build the Server</h3>
            <ol class="step-list">
                <li>Compile the TypeScript code:
                    <pre><code>npm run build</code></pre>
                </li>
                <li>Verify the build succeeded - you should see a <code>dist/index.js</code> file</li>
            </ol>
            
            <h3>Step 4: Test the Server</h3>
            <ol class="step-list">
                <li>Run the server to verify it works:
                    <pre><code>npm start</code></pre>
                </li>
                <li>You should see "Digital ID MCP Server running on stdio"</li>
                <li>Press Ctrl+C to stop the server</li>
            </ol>
            
            <h3>Step 5: Configure Your AI Assistant</h3>
            <p>The configuration depends on which AI assistant you're using:</p>
            
            <h4>For Cursor</h4>
            <ol class="step-list">
                <li>Open Cursor settings</li>
                <li>Navigate to MCP settings (usually in Settings → Features → MCP)</li>
                <li>Add the following configuration to your MCP settings file:
                    <pre><code>{
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
    }
  }
}</code></pre>
                </li>
                <li><strong>Important:</strong> Replace <code>/absolute/path/to/digital-id</code> with the actual absolute path to your project directory</li>
                <li>Replace database credentials with your actual values</li>
                <li><strong>Required:</strong> Add <code>"ORGANISATION_ID": "1"</code> with your organisation ID (replace 1 with your actual organisation ID). The MCP server requires this to be set.</li>
                <li>Restart Cursor for changes to take effect</li>
            </ol>
            
            <h4>For Claude Desktop</h4>
            <ol class="step-list">
                <li>Open Claude Desktop settings</li>
                <li>Navigate to MCP settings (usually in Settings → Developer → MCP)</li>
                <li>Add the same configuration as shown for Cursor above</li>
                <li>Use the absolute path to <code>dist/index.js</code></li>
                <li>Restart Claude Desktop</li>
            </ol>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Finding Your Path</h4>
                <p>On macOS/Linux, you can find the absolute path by running <code>pwd</code> in the terminal while in your project directory. On Windows, use the full path including the drive letter (e.g., <code>C:\Users\YourName\digital-id\mcp-server\dist\index.js</code>).</p>
            </div>
            
            <h2>Using the MCP Server</h2>
            <p>Once configured, you can interact with your Digital ID system using natural language in your AI assistant:</p>
            
            <h3>Example Queries</h3>
            <ul>
                <li>"Get employee information for john.doe@example.com"</li>
                <li>"Show me verification logs for organisation ID 1 from last month"</li>
                <li>"List all employees with pending photo approvals"</li>
                <li>"Verify this ID card token: abc123..."</li>
                <li>"How many active employees are in organisation 2?"</li>
                <li>"Show me all failed verifications from yesterday"</li>
                <li>"Revoke the ID card for employee ID 5, reason: employee left"</li>
            </ul>
            
            <h2>Architecture</h2>
            <p>Understanding how the MCP server works helps with troubleshooting and customisation:</p>
            
            <h3>Communication Flow</h3>
            <ol>
                <li><strong>AI Assistant:</strong> Receives user query in natural language</li>
                <li><strong>AI Assistant:</strong> Decides which MCP tool to call based on the query</li>
                <li><strong>MCP Server:</strong> Receives tool call request via JSON-RPC over stdio</li>
                <li><strong>MCP Server:</strong> Executes database query using provided parameters</li>
                <li><strong>MCP Server:</strong> Formats results as JSON</li>
                <li><strong>AI Assistant:</strong> Receives structured data and presents it to the user</li>
            </ol>
            
            <h3>Database Connection</h3>
            <p>The server maintains a single MySQL connection that is reused for all requests. The connection is created on first use and persists for the lifetime of the server process.</p>
            
            <h3>Error Handling</h3>
            <p>All errors are caught and returned in a standardised format that AI assistants can understand. Database errors, validation errors, and missing data are all handled gracefully.</p>
            
            <h2>Development Mode</h2>
            <p>For development, you can use watch mode to automatically rebuild when code changes:</p>
            <pre><code>npm run dev</code></pre>
            <p>This runs TypeScript compiler in watch mode, automatically recompiling when you save changes to <code>src/index.ts</code>.</p>
            
            <h2>Adding New Tools</h2>
            <p>To add custom functionality to the MCP server:</p>
            <ol class="step-list">
                <li>
                    <strong>Add Tool Definition:</strong> Add a new tool object to the <code>ListToolsRequestSchema</code> handler in <code>src/index.ts</code>
                    <ul>
                        <li>Define the tool name, description, and input schema</li>
                        <li>Specify required and optional parameters</li>
                    </ul>
                </li>
                <li>
                    <strong>Implement Tool Handler:</strong> Add a case in the <code>CallToolRequestSchema</code> handler switch statement
                    <ul>
                        <li>Extract parameters from the request</li>
                        <li>Execute database queries</li>
                        <li>Return formatted JSON results</li>
                    </ul>
                </li>
                <li>
                    <strong>Rebuild:</strong> Run <code>npm run build</code> to compile changes
                </li>
                <li>
                    <strong>Restart:</strong> Restart your AI assistant to load the updated MCP server
                </li>
            </ol>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Code Structure</h4>
                <p>The MCP server code is well-organised in <code>src/index.ts</code>. Tool definitions are at the top in the <code>ListToolsRequestSchema</code> handler, and implementations are in the <code>CallToolRequestSchema</code> handler. Follow the existing patterns for consistency.</p>
            </div>
            
            <h2>Troubleshooting</h2>
            
            <h3>Server Won't Start</h3>
            <ul>
                <li>Check that Node.js 18+ is installed: <code>node --version</code></li>
                <li>Verify database credentials in <code>.env</code> file</li>
                <li>Ensure the database server is running and accessible</li>
                <li>Check network connectivity if using a remote database</li>
            </ul>
            
            <h3>"Cannot Find Module" Errors</h3>
            <ul>
                <li>Run <code>npm install</code> again to ensure dependencies are installed</li>
                <li>Verify you're in the <code>mcp-server</code> directory</li>
                <li>Check that <code>dist/index.js</code> exists after building</li>
                <li>Ensure <code>node_modules</code> directory exists</li>
            </ul>
            
            <h3>Database Connection Errors</h3>
            <ul>
                <li>Verify database credentials are correct</li>
                <li>Check that the database server is running</li>
                <li>Ensure network access to the database (if remote)</li>
                <li>Verify database name, username, and password</li>
                <li>Check firewall settings if connecting remotely</li>
            </ul>
            
            <h3>Tools Not Appearing in AI Assistant</h3>
            <ul>
                <li>Restart your AI assistant application completely</li>
                <li>Check the MCP configuration syntax is valid JSON</li>
                <li>Verify the path to <code>dist/index.js</code> is correct and absolute</li>
                <li>Check AI assistant logs for MCP connection errors</li>
                <li>Ensure the MCP server starts without errors when tested manually</li>
            </ul>
            
            <h3>Tools Return Errors</h3>
            <ul>
                <li>Check database schema matches what the code expects</li>
                <li>Verify table names and column names are correct</li>
                <li>Check database user has necessary permissions</li>
                <li>Review error messages in AI assistant output for details</li>
            </ul>
            
            <h2>Security Considerations</h2>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Organisation-Level Security</h4>
                <p><strong>The MCP server enforces organisation-level access control.</strong> When configured with <code>ORGANISATION_ID</code>, the server automatically restricts all queries to that organisation only, ensuring:</p>
                <ul style="margin-top: 0.75rem;">
                    <li><strong>Single organisation access</strong> - Can only access the configured organisation's data</li>
                    <li><strong>No cross-organisation access</strong> - Cannot view other organisations' data</li>
                    <li><strong>Automatic filtering</strong> - All queries are filtered by organisation ID</li>
                    <li><strong>Safe for multi-tenant</strong> - Each organisation can have its own MCP server instance</li>
                </ul>
                <p style="margin-top: 0.75rem;"><strong>Security Model:</strong> The MCP server requires database credentials and a configured <code>ORGANISATION_ID</code>. Once configured, it can only access data from the specified organisation. This provides organisation-level isolation for multi-tenant deployments.</p>
            </div>
            
            <h3>Security Best Practices</h3>
            <p>To secure the MCP server, follow these recommendations:</p>
            <ol class="step-list">
                <li>
                    <strong>Configure Organisation ID (Required):</strong> Set <code>ORGANISATION_ID</code> in your environment - this is mandatory
                    <ul style="list-style-type: disc; margin-left: 1.5rem; margin-top: 0.5rem;">
                        <li>The MCP server requires <code>ORGANISATION_ID</code> to be set - it will not start without it</li>
                        <li>Automatically filters all queries to the specified organisation</li>
                        <li>Prevents access to other organisations' data</li>
                        <li>Essential for multi-tenant deployments</li>
                        <li>Example: Add <code>ORGANISATION_ID=1</code> to your <code>.env</code> file</li>
                    </ul>
                </li>
                <li>
                    <strong>Use Read-Only Database Users:</strong> For production, create a read-only database user that can only SELECT data, not modify it
                    <ul style="list-style-type: disc; margin-left: 1.5rem; margin-top: 0.5rem;">
                        <li>This prevents users from modifying data via the MCP server</li>
                        <li>They can still query all data, but cannot revoke cards or make changes</li>
                        <li>Example MySQL: <code>GRANT SELECT ON digital_ids.* TO 'mcp_readonly'@'localhost';</code></li>
                    </ul>
                </li>
                <li>
                    <strong>Restrict Database Access:</strong> Only provide database credentials to trusted administrators
                    <ul style="list-style-type: disc; margin-left: 1.5rem; margin-top: 0.5rem;">
                        <li>The MCP server is designed for administrators who already have database access</li>
                        <li>Do not share database credentials with end users</li>
                        <li>Use separate credentials for MCP server if possible</li>
                    </ul>
                </li>
                <li>
                    <strong>Protect Credentials:</strong> Never commit <code>.env</code> files or database credentials to version control
                    <ul style="list-style-type: disc; margin-left: 1.5rem; margin-top: 0.5rem;">
                        <li>The <code>.gitignore</code> file excludes <code>.env</code> files</li>
                        <li>Use secure methods to share credentials (password managers, secure channels)</li>
                    </ul>
                </li>
                <li>
                    <strong>Network Security:</strong> The server runs locally via stdio (not HTTP), but still ensure network security
                    <ul style="list-style-type: disc; margin-left: 1.5rem; margin-top: 0.5rem;">
                        <li>MCP servers communicate via stdio, so they're not directly accessible over the network</li>
                        <li>However, if database is remote, secure the database connection</li>
                        <li>Use SSH tunnels or VPNs for remote database access</li>
                    </ul>
                </li>
                <li>
                    <strong>Audit Access:</strong> Monitor who has access to database credentials
                    <ul style="list-style-type: disc; margin-left: 1.5rem; margin-top: 0.5rem;">
                        <li>Keep records of who has database credentials</li>
                        <li>Review database access logs regularly</li>
                        <li>Rotate credentials periodically</li>
                    </ul>
                </li>
                <li>
                    <strong>Multi-Tenant Deployments:</strong> The MCP server is designed for organisation-wide access
                    <ul style="list-style-type: disc; margin-left: 1.5rem; margin-top: 0.5rem;">
                        <li><code>ORGANISATION_ID</code> is required - the server will not start without it</li>
                        <li>Each organisation must have its own MCP server instance with its own <code>ORGANISATION_ID</code></li>
                        <li>This ensures complete isolation between organisations</li>
                        <li>No risk of cross-organisation data access</li>
                    </ul>
                </li>
            </ol>
            
            <div class="success-box">
                <h4><i class="fas fa-check-circle"></i> Organisation-Wide Access Enforced</h4>
                <p>The MCP server enforces organisation-wide access by requiring <code>ORGANISATION_ID</code> to be set. This ensures all queries are automatically filtered to the specified organisation, providing essential security for multi-tenant deployments. This feature is described in detail in the setup instructions above.</p>
            </div>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Future Improvements</h4>
                <p>Potential security enhancements for future versions could include:</p>
                <ul style="margin-top: 0.5rem;">
                    <li>User-based authentication (require login credentials)</li>
                    <li>Role-based access control (restrict certain tools based on user role)</li>
                    <li>API keys for authentication</li>
                    <li>Rate limiting to prevent abuse</li>
                </ul>
            </div>
            
            <h2>Benefits of MCP Integration</h2>
            <ul>
                <li><strong>Natural Language Queries:</strong> Ask questions about your Digital ID system in plain English</li>
                <li><strong>Quick Data Access:</strong> Instantly retrieve employee information without navigating the web interface</li>
                <li><strong>Automated Tasks:</strong> Perform routine administrative tasks through AI assistants</li>
                <li><strong>Data Analysis:</strong> Query verification logs and analyse patterns using natural language</li>
                <li><strong>Integration:</strong> Connect Digital ID data with other tools and workflows</li>
            </ul>
            
            <div class="success-box">
                <h4><i class="fas fa-check-circle"></i> Use Cases</h4>
                <p>The MCP server is particularly useful for:</p>
                <ul style="margin-top: 0.5rem;">
                    <li>Administrators who want to quickly look up employee information</li>
                    <li>Analysing verification patterns and security events</li>
                    <li>Integrating Digital ID data into automated workflows</li>
                    <li>Building custom reports and dashboards</li>
                    <li>Auditing and compliance reviews</li>
                </ul>
            </div>
            
    </main>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>
