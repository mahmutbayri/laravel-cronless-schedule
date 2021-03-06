<?php

namespace Spatie\CronlessSchedule\Commands;

use Clue\React\Stdio\Stdio;
use Illuminate\Console\Command;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;

class CronlessScheduleRunCommand extends Command
{
    public $signature = 'cronless-schedule:run {--frequency=60}';

    public $description = 'Run the scheduler';

    protected ?LoopInterface $loop = null;

    public function handle()
    {
        $loop = $this->loop ?? Factory::create();

        $frequency = $this->option('frequency');

        $this
            ->outputHeader($frequency)
            ->scheduleCommand($loop, $frequency)
            ->registerKeypressHandler($loop)
            ->runSchedule();

        $loop->run();
    }

    protected function outputHeader(int $frequency): self
    {
        $this->comment("Will execute schedule:run every {$frequency} seconds...");
        $this->comment("Press enter to manually invoke a run...");
        $this->comment('-------------------------------------------------------');
        $this->comment('');

        return $this;
    }

    protected function scheduleCommand(LoopInterface $loop, int $frequency): self
    {
        $loop->addPeriodicTimer($frequency, fn () => $this->runSchedule());

        return $this;
    }

    protected function registerKeypressHandler(LoopInterface $loop): self
    {
        $stdio = new Stdio($loop);

        $stdio->setEcho(false);

        $stdio->on('data', fn () => $this->runSchedule());

        return $this;
    }

    protected function runSchedule()
    {
        $this->comment($this->timestamp('Running schedule...'));

        $this->call('schedule:run');

        $this->comment($this->timestamp('Schedule run finished.'));
        $this->comment('');
    }

    protected function timestamp(string $message): string
    {
        $currentTime = now()->format('Y-m-d H:i:s');

        return "[{$currentTime}] - {$message}";
    }


    public function useLoop(LoopInterface $loop)
    {
        $this->loop = $loop;
    }
}
