#!/usr/bin/env node

import { Server } from "@modelcontextprotocol/sdk/server/index.js";
import { StdioServerTransport } from "@modelcontextprotocol/sdk/server/stdio.js";
import {
  CallToolRequestSchema,
  ListToolsRequestSchema,
  ListResourcesRequestSchema,
  ReadResourceRequestSchema,
} from "@modelcontextprotocol/sdk/types.js";
import mysql from "mysql2/promise";
import * as dotenv from "dotenv";
import * as path from "path";
import { fileURLToPath } from "url";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Load environment variables
dotenv.config({ path: path.join(__dirname, "..", ".env") });

// Database connection
let dbConnection: mysql.Connection | null = null;

// Get organisation ID from environment (required)
// All queries are restricted to this organisation only for security
function getOrganisationId(): number {
  const orgId = process.env.ORGANISATION_ID;
  if (!orgId) {
    throw new Error("ORGANISATION_ID environment variable is required. The MCP server only supports organisation-wide access for security.");
  }
  const parsed = parseInt(orgId, 10);
  if (isNaN(parsed) || parsed <= 0) {
    throw new Error(`Invalid ORGANISATION_ID: "${orgId}". Must be a positive integer.`);
  }
  return parsed;
}

async function getDbConnection(): Promise<mysql.Connection> {
  if (!dbConnection) {
    const host = process.env.DB_HOST || "localhost";
    const database = process.env.DB_NAME || "digital_ids";
    const user = process.env.DB_USER || "root";
    const password = process.env.DB_PASS || "";

    dbConnection = await mysql.createConnection({
      host,
      database,
      user,
      password,
      charset: "utf8mb4",
    });
  }
  return dbConnection;
}

// Initialize MCP Server
const server = new Server(
  {
    name: "digital-id-mcp-server",
    version: "1.0.0",
  },
  {
    capabilities: {
      tools: {},
      resources: {},
    },
  }
);

// List available tools
server.setRequestHandler(ListToolsRequestSchema, async () => {
  return {
    tools: [
      {
        name: "get_employee",
        description: "Get employee information by ID, email, or employee reference",
        inputSchema: {
          type: "object",
          properties: {
            employee_id: {
              type: "number",
              description: "Employee ID",
            },
            email: {
              type: "string",
              description: "Employee email address",
            },
            employee_reference: {
              type: "string",
              description: "Employee reference number",
            },
            organisation_id: {
              type: "number",
              description: "Filter by organisation ID",
            },
          },
        },
      },
      {
        name: "verify_id_card",
        description: "Verify an ID card using QR code token or NFC token",
        inputSchema: {
          type: "object",
          properties: {
            token: {
              type: "string",
              description: "QR code or NFC verification token",
            },
            verification_type: {
              type: "string",
              enum: ["qr", "nfc", "ble"],
              description: "Type of verification",
              default: "qr",
            },
          },
          required: ["token"],
        },
      },
      {
        name: "get_verification_logs",
        description: "Get verification logs with optional filters",
        inputSchema: {
          type: "object",
          properties: {
            employee_id: {
              type: "number",
              description: "Filter by employee ID",
            },
            organisation_id: {
              type: "number",
              description: "Filter by organisation ID",
            },
            verification_type: {
              type: "string",
              enum: ["visual", "qr", "nfc", "ble"],
              description: "Filter by verification type",
            },
            result: {
              type: "string",
              enum: ["success", "failed"],
              description: "Filter by result",
            },
            start_date: {
              type: "string",
              description: "Start date (YYYY-MM-DD)",
            },
            end_date: {
              type: "string",
              description: "End date (YYYY-MM-DD)",
            },
            limit: {
              type: "number",
              description: "Maximum number of results",
              default: 100,
            },
          },
        },
      },
      {
        name: "list_employees",
        description: "List employees with optional filters",
        inputSchema: {
          type: "object",
          properties: {
            organisation_id: {
              type: "number",
              description: "Filter by organisation ID",
            },
            is_active: {
              type: "boolean",
              description: "Filter by active status",
            },
            has_photo: {
              type: "boolean",
              description: "Filter by whether employee has an approved photo",
            },
            limit: {
              type: "number",
              description: "Maximum number of results",
              default: 100,
            },
          },
        },
      },
      {
        name: "get_organisation",
        description: "Get organisation information",
        inputSchema: {
          type: "object",
          properties: {
            organisation_id: {
              type: "number",
              description: "Organisation ID",
            },
            domain: {
              type: "string",
              description: "Organisation domain",
            },
          },
        },
      },
      {
        name: "revoke_id_card",
        description: "Revoke an employee's ID card",
        inputSchema: {
          type: "object",
          properties: {
            employee_id: {
              type: "number",
              description: "Employee ID",
            },
            reason: {
              type: "string",
              description: "Reason for revocation",
            },
          },
          required: ["employee_id"],
        },
      },
      {
        name: "get_pending_photos",
        description: "Get list of employees with pending photo approvals",
        inputSchema: {
          type: "object",
          properties: {
            organisation_id: {
              type: "number",
              description: "Filter by organisation ID",
            },
          },
        },
      },
    ],
  };
});

// Handle tool calls
server.setRequestHandler(CallToolRequestSchema, async (request) => {
  const { name, arguments: args } = request.params;

  try {
    const db = await getDbConnection();
    const organisationId = getOrganisationId();

    switch (name) {
      case "get_employee": {
        let query = `
          SELECT e.*, u.first_name, u.last_name, u.email, u.is_active as user_active,
                 o.name as organisation_name, o.domain as organisation_domain
          FROM employees e
          JOIN users u ON e.user_id = u.id
          JOIN organisations o ON e.organisation_id = o.id
          WHERE 1=1
        `;
        const params: any[] = [];

        // Always apply organisation restriction for security
        query += " AND e.organisation_id = ?";
        params.push(organisationId);

        if (args.employee_id) {
          query += " AND e.id = ?";
          params.push(args.employee_id);
        } else if (args.email) {
          query += " AND u.email = ?";
          params.push(args.email);
        } else if (args.employee_reference) {
          query += " AND (e.employee_reference = ? OR e.display_reference = ?)";
          params.push(args.employee_reference, args.employee_reference);
        } else {
          return {
            content: [
              {
                type: "text",
                text: "Error: Must provide employee_id, email, or employee_reference",
              },
            ],
            isError: true,
          };
        }

        if (args.organisation_id) {
          query += " AND e.organisation_id = ?";
          params.push(args.organisation_id);
        }

        const [rows] = await db.execute(query, params);
        const employees = rows as any[];

        if (employees.length === 0) {
          return {
            content: [
              {
                type: "text",
                text: "No employee found matching the criteria",
              },
            ],
          };
        }

        return {
          content: [
            {
              type: "text",
              text: JSON.stringify(employees[0], null, 2),
            },
          ],
        };
      }

      case "verify_id_card": {
        const { token, verification_type = "qr" } = args;

        // Get ID card by token
        let cardQuery = `SELECT * FROM id_cards WHERE (qr_token = ? OR nfc_token = ?)`;
        const cardParams: any[] = [token, token];

        // Always apply organisation restriction for security
        cardQuery += ` AND employee_id IN (SELECT id FROM employees WHERE organisation_id = ?)`;
        cardParams.push(organisationId);

        const [cards] = await db.execute(cardQuery, cardParams);
        const idCards = cards as any[];

        if (idCards.length === 0) {
          return {
            content: [
              {
                type: "text",
                text: JSON.stringify({
                  valid: false,
                  reason: "Token not found",
                }),
              },
            ],
          };
        }

        const idCard = idCards[0];

        // Check expiration
        const now = new Date();
        const expiresAt = new Date(idCard.expires_at);
        const tokenExpiresAt = new Date(idCard.token_expires_at);

        if (expiresAt < now) {
          return {
            content: [
              {
                type: "text",
                text: JSON.stringify({
                  valid: false,
                  reason: "ID card expired",
                  expires_at: idCard.expires_at,
                }),
              },
            ],
          };
        }

        if (tokenExpiresAt < now) {
          return {
            content: [
              {
                type: "text",
                text: JSON.stringify({
                  valid: false,
                  reason: "Verification token expired",
                  token_expires_at: idCard.token_expires_at,
                }),
              },
            ],
          };
        }

        // Get employee info
        let empQuery = `SELECT e.*, u.first_name, u.last_name, u.email, o.name as organisation_name
           FROM employees e
           JOIN users u ON e.user_id = u.id
           JOIN organisations o ON e.organisation_id = o.id
           WHERE e.id = ?`;
        const empParams: any[] = [idCard.employee_id];

        // Always apply organisation restriction for security
        empQuery += " AND e.organisation_id = ?";
        empParams.push(organisationId);

        const [employees] = await db.execute(empQuery, empParams);
        const employeeList = employees as any[];

        if (employeeList.length === 0) {
          return {
            content: [
              {
                type: "text",
                text: JSON.stringify({
                  valid: false,
                  reason: "Employee not found",
                }),
              },
            ],
          };
        }

        const employee = employeeList[0];

        // Log verification
        await db.execute(
          `INSERT INTO verification_logs (employee_id, verification_type, result, verified_at, notes)
           VALUES (?, ?, 'success', NOW(), ?)`,
          [employee.id, verification_type, `Verified via MCP server`]
        );

        return {
          content: [
            {
              type: "text",
              text: JSON.stringify(
                {
                  valid: true,
                  employee: {
                    id: employee.id,
                    name: `${employee.first_name} ${employee.last_name}`,
                    email: employee.email,
                    employee_reference: employee.employee_reference,
                    display_reference: employee.display_reference,
                    organisation: employee.organisation_name,
                  },
                  id_card: {
                    id: idCard.id,
                    expires_at: idCard.expires_at,
                  },
                },
                null,
                2
              ),
            },
          ],
        };
      }

      case "get_verification_logs": {
        let query = `
          SELECT vl.*, e.employee_reference, e.display_reference,
                 u.first_name, u.last_name, u.email,
                 o.name as organisation_name
          FROM verification_logs vl
          JOIN employees e ON vl.employee_id = e.id
          JOIN users u ON e.user_id = u.id
          JOIN organisations o ON e.organisation_id = o.id
          WHERE 1=1
        `;
        const params: any[] = [];

        // Always apply organisation restriction for security
        query += " AND e.organisation_id = ?";
        params.push(organisationId);

        if (args.employee_id) {
          query += " AND vl.employee_id = ?";
          params.push(args.employee_id);
        }

        if (args.organisation_id) {
          query += " AND e.organisation_id = ?";
          params.push(args.organisation_id);
        }

        if (args.verification_type) {
          query += " AND vl.verification_type = ?";
          params.push(args.verification_type);
        }

        if (args.result) {
          query += " AND vl.result = ?";
          params.push(args.result);
        }

        if (args.start_date) {
          query += " AND DATE(vl.verified_at) >= ?";
          params.push(args.start_date);
        }

        if (args.end_date) {
          query += " AND DATE(vl.verified_at) <= ?";
          params.push(args.end_date);
        }

        query += " ORDER BY vl.verified_at DESC LIMIT ?";
        params.push(args.limit || 100);

        const [rows] = await db.execute(query, params);
        return {
          content: [
            {
              type: "text",
              text: JSON.stringify(rows, null, 2),
            },
          ],
        };
      }

      case "list_employees": {
        let query = `
          SELECT e.*, u.first_name, u.last_name, u.email, u.is_active as user_active,
                 o.name as organisation_name
          FROM employees e
          JOIN users u ON e.user_id = u.id
          JOIN organisations o ON e.organisation_id = o.id
          WHERE 1=1
        `;
        const params: any[] = [];

        // Always apply organisation restriction for security
        query += " AND e.organisation_id = ?";
        params.push(organisationId);

        if (args.organisation_id) {
          query += " AND e.organisation_id = ?";
          params.push(args.organisation_id);
        }

        if (args.is_active !== undefined) {
          query += " AND e.is_active = ?";
          params.push(args.is_active ? 1 : 0);
        }

        if (args.has_photo !== undefined) {
          if (args.has_photo) {
            query += " AND e.photo_path IS NOT NULL AND e.photo_path != ''";
          } else {
            query += " AND (e.photo_path IS NULL OR e.photo_path = '')";
          }
        }

        query += " ORDER BY e.id DESC LIMIT ?";
        params.push(args.limit || 100);

        const [rows] = await db.execute(query, params);
        return {
          content: [
            {
              type: "text",
              text: JSON.stringify(rows, null, 2),
            },
          ],
        };
      }

      case "get_organisation": {
        // Always restrict to configured organisation
        const query = "SELECT * FROM organisations WHERE id = ?";
        const params = [organisationId];

        const [rows] = await db.execute(query, params);
        const orgs = rows as any[];

        if (orgs.length === 0) {
          return {
            content: [
              {
                type: "text",
                text: "No organisation found",
              },
            ],
          };
        }

        return {
          content: [
            {
              type: "text",
              text: JSON.stringify(orgs[0], null, 2),
            },
          ],
        };
      }

      case "revoke_id_card": {
        const { employee_id, reason } = args;

        // Verify employee belongs to the configured organisation
        const [employees] = await db.execute(
          `SELECT organisation_id FROM employees WHERE id = ?`,
          [employee_id]
        );
        const empList = employees as any[];
        if (empList.length === 0 || empList[0].organisation_id !== organisationId) {
          return {
            content: [
              {
                type: "text",
                text: JSON.stringify({
                  success: false,
                  message: "Employee not found or access denied",
                }),
              },
            ],
            isError: true,
          };
        }

        // Update employee to revoke ID card
        await db.execute(
          `UPDATE employees SET is_active = 0 WHERE id = ?`,
          [employee_id]
        );

        // Update ID card expiration
        await db.execute(
          `UPDATE id_cards SET expires_at = NOW() WHERE employee_id = ?`,
          [employee_id]
        );

        return {
          content: [
            {
              type: "text",
              text: JSON.stringify({
                success: true,
                message: `ID card revoked for employee ${employee_id}`,
                reason: reason || "No reason provided",
              }),
            },
          ],
        };
      }

      case "get_pending_photos": {
        let query = `
          SELECT e.*, u.first_name, u.last_name, u.email,
                 o.name as organisation_name
          FROM employees e
          JOIN users u ON e.user_id = u.id
          JOIN organisations o ON e.organisation_id = o.id
          WHERE e.photo_approval_status = 'pending'
          AND e.photo_pending_path IS NOT NULL
        `;
        const params: any[] = [];

        // Always apply organisation restriction for security
        query += " AND e.organisation_id = ?";
        params.push(organisationId);

        if (args.organisation_id) {
          query += " AND e.organisation_id = ?";
          params.push(args.organisation_id);
        }

        query += " ORDER BY e.updated_at DESC";

        const [rows] = await db.execute(query, params);
        return {
          content: [
            {
              type: "text",
              text: JSON.stringify(rows, null, 2),
            },
          ],
        };
      }

      default:
        return {
          content: [
            {
              type: "text",
              text: `Unknown tool: ${name}`,
            },
          ],
          isError: true,
        };
    }
  } catch (error: any) {
    return {
      content: [
        {
          type: "text",
          text: `Error: ${error.message}`,
        },
      ],
      isError: true,
    };
  }
});

// List available resources
server.setRequestHandler(ListResourcesRequestSchema, async () => {
  return {
    resources: [
      {
        uri: "digital-id://employees",
        name: "All Employees",
        description: "List of all employees in the system",
        mimeType: "application/json",
      },
      {
        uri: "digital-id://organisations",
        name: "All Organisations",
        description: "List of all organisations",
        mimeType: "application/json",
      },
    ],
  };
});

// Read resources
server.setRequestHandler(ReadResourceRequestSchema, async (request) => {
  const { uri } = request.params;

  try {
    const db = await getDbConnection();
    const organisationId = getOrganisationId();

    if (uri === "digital-id://employees") {
      let query = `
        SELECT e.*, u.first_name, u.last_name, u.email, o.name as organisation_name
        FROM employees e
        JOIN users u ON e.user_id = u.id
        JOIN organisations o ON e.organisation_id = o.id
        WHERE 1=1
      `;
      const params: any[] = [];

      // Always apply organisation restriction for security
      query += " AND e.organisation_id = ?";
      params.push(organisationId);

      query += " ORDER BY e.id DESC LIMIT 1000";

      const [rows] = await db.execute(query, params);
      return {
        contents: [
          {
            uri,
            mimeType: "application/json",
            text: JSON.stringify(rows, null, 2),
          },
        ],
      };
    }

    if (uri === "digital-id://organisations") {
      let query = `SELECT * FROM organisations WHERE 1=1`;
      const params: any[] = [];

      // Always restrict to configured organisation
      query += " AND id = ?";
      params.push(organisationId);

      query += " ORDER BY id DESC";

      const [rows] = await db.execute(query, params);
      return {
        contents: [
          {
            uri,
            mimeType: "application/json",
            text: JSON.stringify(rows, null, 2),
          },
        ],
      };
    }

    return {
      contents: [
        {
          uri,
          mimeType: "text/plain",
          text: `Unknown resource: ${uri}`,
        },
      ],
    };
  } catch (error: any) {
    return {
      contents: [
        {
          uri,
          mimeType: "text/plain",
          text: `Error: ${error.message}`,
        },
      ],
    };
  }
});

// Start server
async function main() {
  // Validate organisation ID is set at startup
  try {
    const orgId = getOrganisationId();
    console.error(`Digital ID MCP Server running on stdio (restricted to organisation ID: ${orgId})`);
  } catch (error: any) {
    console.error(`Fatal error: ${error.message}`);
    process.exit(1);
  }
  
  const transport = new StdioServerTransport();
  await server.connect(transport);
}

main().catch((error) => {
  console.error("Fatal error:", error);
  process.exit(1);
});



