<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Services\MembershipCsvImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class MembershipImportController extends Controller
{
    private const SESSION_ROWS = 'admin.membership_import.rows';

    private const SESSION_FILENAME = 'admin.membership_import.filename';

    public function __construct(
        private readonly MembershipCsvImportService $importService,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()?->hasRole('admin'), 403);

        $preview = null;
        $rows = $request->session()->get(self::SESSION_ROWS);
        if (is_array($rows) && $rows !== []) {
            $preview = $this->importService->analyzeRows(
                $rows,
                (bool) $request->old('update_existing', false)
            );
        }

        return view('admin.migration.index', [
            'preview' => $preview,
            'uploadFilename' => $request->session()->get(self::SESSION_FILENAME),
            'planCodes' => Plan::query()->where('active', true)->orderBy('sort_order')->pluck('code'),
            'columns' => MembershipCsvImportService::ALL_COLUMNS,
            'importResult' => $request->session()->get('membership_import_result'),
        ]);
    }

    public function template(): Response
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);

        $filename = 'herohub-membership-import-template.csv';

        return response($this->importService->sampleCsvContents(), 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function preview(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasRole('admin'), 403);

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $parsed = $this->importService->parseUploadedCsv((string) $request->file('file')->getRealPath());
        if ($parsed['errors'] !== []) {
            return back()->withErrors(['file' => implode(' ', $parsed['errors'])]);
        }

        if ($parsed['rows'] === []) {
            return back()->withErrors(['file' => 'The CSV has headers but no data rows.']);
        }

        $request->session()->put(self::SESSION_ROWS, $parsed['rows']);
        $request->session()->put(self::SESSION_FILENAME, $request->file('file')->getClientOriginalName());
        $request->session()->forget('membership_import_result');

        return redirect()
            ->route('admin.migration.index')
            ->with('status', 'CSV loaded — review the preview below, then run import.');
    }

    public function import(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasRole('admin'), 403);

        $request->validate([
            'update_existing' => ['nullable', 'boolean'],
            'dry_run' => ['nullable', 'boolean'],
        ]);

        $rows = $request->session()->get(self::SESSION_ROWS);
        if (! is_array($rows) || $rows === []) {
            return redirect()
                ->route('admin.migration.index')
                ->withErrors(['file' => 'Upload a CSV and preview it before importing.']);
        }

        $updateExisting = $request->boolean('update_existing');
        $dryRun = $request->boolean('dry_run');

        $analysis = $this->importService->analyzeRows($rows, $updateExisting);
        if ($analysis['summary']['error'] > 0 && ! $dryRun) {
            return back()->withErrors([
                'file' => 'Fix '.$analysis['summary']['error'].' error row(s) before importing, or run a dry run first.',
            ]);
        }

        $result = $this->importService->importRows($rows, $updateExisting, $dryRun);

        if (! $dryRun) {
            $request->session()->forget(self::SESSION_ROWS);
            $request->session()->forget(self::SESSION_FILENAME);
        }

        return redirect()
            ->route('admin.migration.index')
            ->with('membership_import_result', $result)
            ->with('status', $dryRun ? 'Dry run completed.' : 'Import completed.');
    }

    public function reset(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasRole('admin'), 403);

        $request->session()->forget(self::SESSION_ROWS);
        $request->session()->forget(self::SESSION_FILENAME);
        $request->session()->forget('membership_import_result');

        return redirect()
            ->route('admin.migration.index')
            ->with('status', 'Import preview cleared.');
    }
}
