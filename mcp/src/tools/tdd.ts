import { Tool } from "@modelcontextprotocol/sdk/types.js";
import { execSync } from "child_process";

export const tddTool: Tool = {
  name: "tdd_command",
  description:
    "Execute TDD (Test-Driven Development) commands. Run tests, watch tests, or analyze test coverage for the spk_bansos Laravel application.",
  inputSchema: {
    type: "object" as const,
    properties: {
      action: {
        type: "string",
        enum: ["run_all_tests", "run_unit_tests", "run_feature_tests", "run_specific_test", "watch_tests", "coverage"],
        description: "The TDD action to perform",
      },
      testFile: {
        type: "string",
        description: "Specific test file to run (for run_specific_test action). Example: 'tests/Feature/ExampleTest.php'",
      },
      filter: {
        type: "string",
        description: "Filter tests by method name or pattern",
      },
    },
    required: ["action"],
  },
};

export async function executeTddCommand(
  args: Record<string, unknown>
): Promise<unknown> {
  const { action, testFile, filter } = args;

  if (!action || typeof action !== "string") {
    throw new Error("Action is required");
  }

  try {
    let command = "";

    switch (action) {
      case "run_all_tests":
        command = "php artisan test";
        break;

      case "run_unit_tests":
        command = "php artisan test tests/Unit";
        break;

      case "run_feature_tests":
        command = "php artisan test tests/Feature";
        break;

      case "run_specific_test":
        if (!testFile || typeof testFile !== "string") {
          throw new Error("testFile is required for run_specific_test action");
        }
        command = `php artisan test ${testFile}`;
        if (filter && typeof filter === "string") {
          command += ` --filter="${filter}"`;
        }
        break;

      case "watch_tests":
        command = "php artisan test --watch";
        break;

      case "coverage":
        command = "php artisan test --coverage";
        break;

      default:
        throw new Error(`Unknown action: ${action}`);
    }

    // Execute the command
    try {
      const output = execSync(command, {
        cwd: "c:\\Users\\AdminBaru\\Desktop\\spk_bansos",
        encoding: "utf-8",
      });

      return {
        success: true,
        action,
        command,
        output,
      };
    } catch (execError: unknown) {
      const errorOutput = execError instanceof Error ? execError.message : String(execError);
      return {
        success: false,
        action,
        command,
        error: errorOutput,
      };
    }
  } catch (error) {
    throw new Error(`TDD command error: ${error instanceof Error ? error.message : String(error)}`);
  }
}
