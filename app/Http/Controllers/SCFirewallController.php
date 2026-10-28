<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\SCFirewall;
use App\Models\SCFirewallBackup;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SCFirewallController extends Controller
{
    public function index()
    {
        $validated = collect(request()->validate([
            'filterHostname' => 'nullable|string|max:255',
            'filterStatus' => 'nullable|string|in:connected,disconnected,suspended',
        ]))->filter();

        $scfirewalls = SCFirewall::with('SCTenant')
            ->orderBy('hostname')
            ->when($validated->has('filterHostname'), function ($query) use ($validated) {
                $query->whereLike('hostname', '%'.$validated['filterHostname'].'%');
            })
            ->when($validated->has('filterStatus'), function ($query) use ($validated) {
                match ($validated['filterStatus']) {
                    'connected' => $query->connected(),
                    'disconnected' => $query->disconnected(),
                    'suspended' => $query->suspended(),
                };
            })
            ->paginate(50);

        $firewallsCount = [
            'all' => SCFirewall::count(),
            'connected' => SCFirewall::connected()->count(),
            'disconnected' => SCFirewall::disconnected()->count(),
            'suspended' => SCFirewall::suspended()->count(),
        ];

        return view('scfirewalls.index', compact('scfirewalls', 'firewallsCount'));
    }

    public function firewallDetails(string $id)
    {
        $firewall = SCFirewall::with('SCTenant')->findOrFail($id);

        return view('scfirewalls.show.details', compact('firewall'));
    }

    public function firewallRaw(string $id)
    {
        $firewall = SCFirewall::with('SCTenant')->findOrFail($id);

        return view('scfirewalls.show.raw', compact('firewall'));
    }

    public function firewallFirmware(string $id)
    {
        $firewall = SCFirewall::with('SCTenant')->findOrFail($id);

        return view('scfirewalls.show.firmware', compact('firewall'));
    }

    public function firewallBackups(string $id)
    {
        $firewall = SCFirewall::with('SCTenant')->findOrFail($id);

        return view('scfirewalls.show.backups', compact('firewall'));
    }

    public function firewallBackupDownload(string $id, int $backup): StreamedResponse
    {
        $firewall = SCFirewall::findOrFail($id);
        $firewallBackup = $firewall->backups()->findOrFail($backup);

        abort_unless($firewallBackup->isDownloadable(), 404);

        Event::logInfo('scfirewalls', 'Firewall backup downloaded', [
            'firewall' => $firewall->id,
            'backup' => $firewallBackup->id,
            'user' => auth()->user()?->email,
        ]);

        return Storage::disk(SCFirewallBackup::DISK)->download($firewallBackup->file_path, basename($firewallBackup->file_path));
    }
}
