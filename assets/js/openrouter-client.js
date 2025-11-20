import OpenAI from 'openai';

const openai = new OpenAI({
  baseURL: 'https://openrouter.ai/api/v1',
  apiKey: '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',
  defaultHeaders: {
    'HTTP-Referer': 'https://apsdreamhomes.com',
    'X-Title': 'APS Dream Home',
  },
});

async function main() {
  const completion = await openai.chat.completions.create({
    model: 'openai/gpt-4o',
    messages: [
      {
        role: 'user',
        content: 'What is the meaning of life?',
      },
    ],
  });

  console.log(completion.choices[0].message);
}

main();
