<?php

declare(strict_types=1);

namespace Planner\Tests\Infrastructure\Repository;

use Planner\Domain\Model\Day;
use Planner\Domain\Model\FlexibleTask;
use Planner\Domain\Model\ScheduleItem;
use Planner\Infrastructure\Repository\JsonDayRepository;
use PHPUnit\Framework\TestCase;

class JsonDayRepositoryTest extends TestCase
{
    private string $dataPath;
    private JsonDayRepository $repository;

    protected function setUp(): void
    {
        $this->dataPath = sys_get_temp_dir() . '/planner-test-' . uniqid();
        $this->repository = new JsonDayRepository($this->dataPath);
    }

    protected function tearDown(): void
    {
        if (!is_dir($this->dataPath)) {
            return;
        }
        foreach (glob($this->dataPath . '/*.json') ?: [] as $file) {
            unlink($file);
        }
        rmdir($this->dataPath);
    }

    public function testConstructorCreatesDataDirectory(): void
    {
        $path = $this->dataPath . '/nested/dir';

        new JsonDayRepository($path);

        $this->assertDirectoryExists($path);

        rmdir($path);
        rmdir($this->dataPath . '/nested');
    }

    public function testFindByDateReturnsNullWhenFileNotExists(): void
    {
        $this->assertNull($this->repository->findByDate('2026-10-26'));
    }

    public function testSaveAndFindByDate(): void
    {
        $day = new Day('2026-10-26', 'Goal');
        $day->addScheduleItem(new ScheduleItem('s1', '09:00', '10:00', 'Meeting', ['work'], 'pending'));
        $day->addFlexibleTask(new FlexibleTask('t1', 'Task', 30, 'high', ['a'], 'done'));

        $this->repository->save($day);

        $this->assertFileExists($this->dataPath . '/2026-10-26.json');

        $loaded = $this->repository->findByDate('2026-10-26');

        $this->assertSame($day->toArray(), $loaded->toArray());
    }

    public function testSaveOverwritesExistingFile(): void
    {
        $day = new Day('2026-10-26', 'Old goal');
        $this->repository->save($day);

        $day->mainGoal = 'New goal';
        $this->repository->save($day);

        $loaded = $this->repository->findByDate('2026-10-26');
        $this->assertSame('New goal', $loaded->mainGoal);
    }

    public function testExists(): void
    {
        $this->assertFalse($this->repository->exists('2026-10-26'));

        $this->repository->save(new Day('2026-10-26'));

        $this->assertTrue($this->repository->exists('2026-10-26'));
    }

    public function testDelete(): void
    {
        $this->repository->save(new Day('2026-10-26'));

        $this->repository->delete('2026-10-26');

        $this->assertFalse($this->repository->exists('2026-10-26'));
        $this->assertNull($this->repository->findByDate('2026-10-26'));
    }

    public function testDeleteDoesNothingWhenFileNotExists(): void
    {
        $this->repository->delete('2099-01-01');

        $this->assertFalse($this->repository->exists('2099-01-01'));
    }

    public function testSavedJsonIsValidAndPrettyPrinted(): void
    {
        $this->repository->save(new Day('2026-10-26', 'Цель на русском'));

        $content = file_get_contents($this->dataPath . '/2026-10-26.json');
        $data = json_decode($content, true);

        $this->assertSame('2026-10-26', $data['date']);
        // JSON_UNESCAPED_UNICODE: кириллица сохраняется как есть
        $this->assertStringContainsString('Цель на русском', $content);
        // JSON_PRETTY_PRINT: вывод многострочный
        $this->assertStringContainsString("\n", $content);
    }
}
