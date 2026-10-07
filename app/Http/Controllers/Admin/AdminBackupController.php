<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AdminBackupController extends Controller
{
    private string $backupDir;

    public function __construct(private ActivityLogService $activityLog)
    {
        $this->backupDir = storage_path('app/backups');
        if (! File::exists($this->backupDir)) {
            File::makeDirectory($this->backupDir, 0755, true);
        }
    }

    public function index(): View
    {
        $dbPath = database_path('database.sqlite');
        $dbSize = File::exists($dbPath) ? File::size($dbPath) : 0;

        $files = collect(File::files($this->backupDir))
            ->filter(fn($file) => in_array($file->getExtension(), ['sqlite', 'sql']))
            ->map(function ($file) {
                return [
                    'filename'       => $file->getFilename(),
                    'path'           => $file->getPathname(),
                    'size'           => $file->getSize(),
                    'size_formatted' => round($file->getSize() / 1024, 2) . ' KB',
                    'created_at'     => Carbon::createFromTimestamp($file->getMTime()),
                    'is_snapshot'    => str_starts_with($file->getFilename(), 'pre-restore-snapshot'),
                ];
            })
            ->sortByDesc('created_at')
            ->values();

        return view('admin.backups.index', compact('files', 'dbSize'));
    }

    public function store(): RedirectResponse
    {
        $dbPath = database_path('database.sqlite');
        if (! File::exists($dbPath)) {
            return back()->with('error', 'Database file does not exist.');
        }

        $filename = 'backup-sqlite-' . date('Y-m-d_H-i-s') . '.sqlite';
        File::copy($dbPath, $this->backupDir . DIRECTORY_SEPARATOR . $filename);

        $this->activityLog->log('create_backup', 'backup', null, $filename);

        return back()->with('success', "Database backup created: {$filename}");
    }

    public function download(string $filename): BinaryFileResponse|RedirectResponse
    {
        $filename = basename($filename);
        $fullPath = $this->backupDir . DIRECTORY_SEPARATOR . $filename;

        if (! File::exists($fullPath)) {
            return back()->with('error', 'Backup file not found.');
        }

        $this->activityLog->log('download_backup', 'backup', null, $filename);

        return response()->download($fullPath);
    }

    public function restore(Request $request, string $filename): RedirectResponse
    {
        $request->validate([
            'confirmation' => ['required', 'string', 'in:RESTORE'],
        ], [
            'confirmation.in' => 'You must type "RESTORE" in capital letters to confirm database restore.',
        ]);

        $filename = basename($filename);
        $sourcePath = $this->backupDir . DIRECTORY_SEPARATOR . $filename;
        $targetDb = database_path('database.sqlite');

        if (! File::exists($sourcePath)) {
            return back()->with('error', 'Backup file not found.');
        }

        // 1. Take Pre-Restore Snapshot of current DB
        if (File::exists($targetDb)) {
            $snapshotName = 'pre-restore-snapshot-' . date('Y-m-d_H-i-s') . '.sqlite';
            File::copy($targetDb, $this->backupDir . DIRECTORY_SEPARATOR . $snapshotName);
        }

        // 2. Restore file
        File::copy($sourcePath, $targetDb);

        $this->activityLog->log('restore_backup', 'backup', null, "Restored from {$filename} with safety snapshot");

        return back()->with('success', "Database successfully restored from {$filename}. An automatic pre-restore safety snapshot was captured.");
    }

    public function destroy(string $filename): RedirectResponse
    {
        $filename = basename($filename);
        $fullPath = $this->backupDir . DIRECTORY_SEPARATOR . $filename;

        if (File::exists($fullPath)) {
            File::delete($fullPath);
            $this->activityLog->log('delete_backup', 'backup', null, $filename);
            return back()->with('success', "Backup file {$filename} deleted.");
        }

        return back()->with('error', 'File not found.');
    }
}

