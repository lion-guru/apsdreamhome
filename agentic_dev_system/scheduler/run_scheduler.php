<?php

namespace App\Core\Agentic;

class ContinuousScheduler
{
    private Orchestrator $orchestrator;
    private TaskDiscovery $discovery;
    private OllamaClient $ollama;
    private int $cycleCount = 0;
    private int $maxCycles;
    private int $intervalMs;
    private bool $running = true;

    public function __construct()
    {
        $config = json_decode(file_get_contents(__DIR__ . '/../config.json'), true);
        $this->orchestrator = new Orchestrator();
        $this->discovery = new TaskDiscovery($config['project_root']);
        $this->ollama = new OllamaClient($config['ollama']);
        $this->maxCycles = $config['scheduler']['max_cycles_per_run'];
        $this->intervalMs = $config['scheduler']['cycle_interval_ms'];
    }

    public function start(): void
    {
        echo "\n" . str_repeat('=', 60) . "\n";
        echo "  APS Dream Home - Autonomous Agentic Dev System\n";
        echo "  Started: " . date('Y-m-d H:i:s') . "\n";
        echo "  Mode: Continuous (stop with Ctrl+C)\n";
        echo str_repeat('=', 60) . "\n\n";

        $this->log("System initialized - Ollama: " . ($this->ollama->isAvailable() ? 'available' : 'not available'));
        $this->log("Loaded " . count($this->discovery->discover()) . " initial tasks");

        while ($this->running && $this->cycleCount < $this->maxCycles) {
            $this->cycleCount++;
            $cycleStart = microtime(true);

            try {
                if ($this->ollama->isAvailable()) {
                    $model = $this->ollama->getModel();
                    if ($model !== '') {
                        $this->log("AI backend: {$model}");
                    }
                }

                $tasks = $this->discovery->discover();
                if (!empty($tasks)) {
                    $this->log("Cycle {$this->cycleCount}: " . count($tasks) . " task(s) found");
                    $this->orchestrator->run();
                } else {
                    $this->orchestrator->run();
                    $idleTime = round((microtime(true) - $cycleStart) * 1000);
                    $this->log("Cycle {$this->cycleCount}: idle ({$idleTime}ms)");
                }
            } catch (\Exception $e) {
                $this->log("ERROR: " . $e->getMessage());
            }

            usleep($this->intervalMs * 1000);
        }

        $this->log("Scheduler stopped after {$this->cycleCount} cycles");
    }

    private function log(string $message): void
    {
        $line = "[" . date('Y-m-d H:i:s') . "] {$message}\n";
        file_put_contents(__DIR__ . '/../logs/scheduler.log', $line, FILE_APPEND);
        echo $line;
    }
}

if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($argv[0] ?? '')) {
    require __DIR__ . '/../config/bootstrap.php';
    $scheduler = new ContinuousScheduler();
    $scheduler->start();
}