import mysql from "mysql2/promise";
import { Tool } from "@modelcontextprotocol/sdk/types.js";

// MySQL connection pool
let pool: mysql.Pool | null = null;

// Initialize connection pool
function initializePool(config: {
  host: string;
  user: string;
  password: string;
  database: string;
  port?: number;
}): mysql.Pool {
  if (pool) {
    return pool;
  }

  pool = mysql.createPool({
    host: config.host || "localhost",
    user: config.user || "root",
    password: config.password || "",
    database: config.database,
    port: config.port || 3306,
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0,
  });

  return pool;
}

export const mysqlTool: Tool = {
  name: "mysql_query",
  description:
    "Execute queries against XAMPP MySQL database spk_bansos. Supports SELECT, INSERT, UPDATE, DELETE operations.",
  inputSchema: {
    type: "object" as const,
    properties: {
      query: {
        type: "string",
        description: "SQL query to execute",
      },
      params: {
        type: "array",
        description: "Query parameters for prepared statements",
        items: {
          type: ["string", "number", "boolean", "null"],
        },
      },
    },
    required: ["query"],
  },
};

export async function executeMysqlQuery(
  args: Record<string, unknown>
): Promise<unknown> {
  const { query, params } = args;

  if (!query || typeof query !== "string") {
    throw new Error("Query is required");
  }

  const poolConfig = {
    host: "localhost",
    user: "root",
    password: "",
    database: "spk_bansos",
    port: 3306,
  };

  const currentPool = initializePool(poolConfig);
  const connection = await currentPool.getConnection();

  try {
    let result;
    if (Array.isArray(params) && params.length > 0) {
      result = await connection.execute(query, params);
    } else {
      result = await connection.execute(query);
    }

    return {
      success: true,
      rows: result[0],
      affectedRows: (result[1] as { affectedRows?: number })?.affectedRows || 0,
    };
  } catch (error) {
    throw new Error(`Database error: ${error instanceof Error ? error.message : String(error)}`);
  } finally {
    connection.release();
  }
}
