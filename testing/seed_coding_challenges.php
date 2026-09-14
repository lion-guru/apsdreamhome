<?php
require 'vendor/autoload.php';

use App\Core\Database\Database;

$pdo = Database::getInstance()->getConnection();

$challenges = [
    [
        'title' => 'Two Sum',
        'slug' => 'two-sum',
        'description' => 'Find two numbers that add up to target',
        'difficulty' => 'easy',
        'category' => 'arrays',
        'problem_statement' => 'Given an array of integers nums and an integer target, return indices of the two numbers such that they add up to target.',
        'input_format' => 'First line: n (array size)\nSecond line: n integers\nThird line: target',
        'output_format' => 'Two space-separated indices',
        'constraints' => '2 <= nums.length <= 10^4\n-10^9 <= nums[i] <= 10^9',
        'sample_input' => "4\n2 7 11 15\n9",
        'sample_output' => '0 1',
        'explanation' => 'Use a hash map to store value->index mapping. For each element, check if target - element exists in map.',
        'starter_code' => 'function twoSum(nums, target) {\n  // Your code here\n}',
        'solution_code' => 'function twoSum(nums, target) {\n  const map = new Map();\n  for (let i = 0; i < nums.length; i++) {\n    const complement = target - nums[i];\n    if (map.has(complement)) {\n      return [map.get(complement), i];\n    }\n    map.set(nums[i], i);\n  }\n  return [];\n}',
        'time_limit_seconds' => 2,
        'memory_limit_mb' => 256,
        'points' => 10,
        'is_active' => 1,
        'created_by' => 1,
    ],
    [
        'title' => 'Reverse String',
        'slug' => 'reverse-string',
        'description' => 'Reverse a string in place',
        'difficulty' => 'easy',
        'category' => 'strings',
        'problem_statement' => 'Write a function that reverses a string. The input string is given as an array of characters s.',
        'input_format' => 'First line: string',
        'output_format' => 'Reversed string',
        'constraints' => '1 <= s.length <= 10^5',
        'sample_input' => 'hello',
        'sample_output' => 'olleh',
        'explanation' => 'Use two pointers approach - swap characters from start and end moving towards center.',
        'starter_code' => 'function reverseString(s) {\n  // Your code here\n}',
        'solution_code' => 'function reverseString(s) {\n  let left = 0, right = s.length - 1;\n  while (left < right) {\n    [s[left], s[right]] = [s[right], s[left]];\n    left++;\n    right--;\n  }\n  return s.join("");\n}',
        'time_limit_seconds' => 1,
        'memory_limit_mb' => 256,
        'points' => 10,
        'is_active' => 1,
        'created_by' => 1,
    ],
    [
        'title' => 'Valid Parentheses',
        'slug' => 'valid-parentheses',
        'description' => 'Check if parentheses string is valid',
        'difficulty' => 'easy',
        'category' => 'stack',
        'problem_statement' => 'Given a string s containing just the characters (, ), {, }, [, ], determine if the input string is valid.',
        'input_format' => 'First line: string',
        'output_format' => 'true or false',
        'constraints' => '1 <= s.length <= 10^4',
        'sample_input' => '()[]{}',
        'sample_output' => 'true',
        'explanation' => 'Use a stack. Push opening brackets, pop and match for closing brackets.',
        'starter_code' => 'function isValid(s) {\n  // Your code here\n}',
        'solution_code' => 'function isValid(s) {\n  const stack = [];\n  const map = {")": "(", "}": "{", "]": "["};\n  for (let char of s) {\n    if (map[char]) {\n      if (stack.pop() !== map[char]) return false;\n    } else {\n      stack.push(char);\n    }\n  }\n  return stack.length === 0;\n}',
        'time_limit_seconds' => 2,
        'memory_limit_mb' => 256,
        'points' => 15,
        'is_active' => 1,
        'created_by' => 1,
    ],
    [
        'title' => 'Binary Search',
        'slug' => 'binary-search',
        'description' => 'Implement binary search',
        'difficulty' => 'medium',
        'category' => 'search',
        'problem_statement' => 'Given a sorted array of integers nums and a target value, return the index if target is found. If not, return -1.',
        'input_format' => 'First line: n\nSecond line: n sorted integers\nThird line: target',
        'output_format' => 'Index or -1',
        'constraints' => '1 <= nums.length <= 10^4\n-10^4 <= nums[i], target <= 10^4',
        'sample_input' => "6\n-1 0 3 5 9 12\n9",
        'sample_output' => '4',
        'explanation' => 'Classic binary search - maintain left/right pointers, adjust based on mid comparison.',
        'starter_code' => 'function search(nums, target) {\n  // Your code here\n}',
        'solution_code' => 'function search(nums, target) {\n  let left = 0, right = nums.length - 1;\n  while (left <= right) {\n    const mid = Math.floor((left + right) / 2);\n    if (nums[mid] === target) return mid;\n    if (nums[mid] < target) left = mid + 1;\n    else right = mid - 1;\n  }\n  return -1;\n}',
        'time_limit_seconds' => 2,
        'memory_limit_mb' => 256,
        'points' => 20,
        'is_active' => 1,
        'created_by' => 1,
    ],
    [
        'title' => 'Maximum Subarray',
        'slug' => 'maximum-subarray',
        'description' => 'Find maximum sum of contiguous subarray',
        'difficulty' => 'medium',
        'category' => 'dynamic-programming',
        'problem_statement' => 'Given an integer array nums, find the subarray with the largest sum and return its sum.',
        'input_format' => 'First line: n\nSecond line: n integers',
        'output_format' => 'Maximum sum',
        'constraints' => '1 <= nums.length <= 10^5\n-10^4 <= nums[i] <= 10^4',
        'sample_input' => "9\n-2 1 -3 4 -1 2 1 -5 4",
        'sample_output' => '6',
        'explanation' => "Kadane's algorithm - maintain current max and global max.",
        'starter_code' => 'function maxSubArray(nums) {\n  // Your code here\n}',
        'solution_code' => 'function maxSubArray(nums) {\n  let maxCurrent = nums[0], maxGlobal = nums[0];\n  for (let i = 1; i < nums.length; i++) {\n    maxCurrent = Math.max(nums[i], maxCurrent + nums[i]);\n    maxGlobal = Math.max(maxGlobal, maxCurrent);\n  }\n  return maxGlobal;\n}',
        'time_limit_seconds' => 2,
        'memory_limit_mb' => 256,
        'points' => 25,
        'is_active' => 1,
        'created_by' => 1,
    ],
];

$pdo = Database::getInstance()->getConnection();
$stmt = $pdo->prepare("
    INSERT INTO coding_challenges (title, slug, description, difficulty, category, problem_statement, input_format, output_format, constraints, sample_input, sample_output, explanation, starter_code, solution_code, time_limit_seconds, memory_limit_mb, points, is_active, created_by)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

foreach ($challenges as $c) {
    $stmt->execute([
        $c['title'], $c['slug'], $c['description'], $c['difficulty'], $c['category'],
        $c['problem_statement'], $c['input_format'], $c['output_format'], $c['constraints'],
        $c['sample_input'], $c['sample_output'], $c['explanation'], $c['starter_code'],
        $c['solution_code'], $c['time_limit_seconds'], $c['memory_limit_mb'], $c['points'],
        $c['is_active'], $c['created_by']
    ]);
    echo "Inserted: {$c['title']}\n";
}
echo "Done!\n";