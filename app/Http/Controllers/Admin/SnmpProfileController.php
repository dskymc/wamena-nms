<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SnmpSecurityLevel;
use App\Enums\SnmpVersion;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSnmpProfileRequest;
use App\Http\Requests\Admin\UpdateSnmpProfileRequest;
use App\Models\SnmpProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SnmpProfileController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', SnmpProfile::class);

        $profiles = SnmpProfile::query()
            ->withCount('devices')
            ->when(request('search'), fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.snmp-profiles.index', compact('profiles'));
    }

    public function create(): View
    {
        $this->authorize('create', SnmpProfile::class);

        return view('admin.snmp-profiles.create', [
            'versions' => SnmpVersion::cases(),
            'securityLevels' => SnmpSecurityLevel::cases(),
        ]);
    }

    public function store(StoreSnmpProfileRequest $request): RedirectResponse
    {
        SnmpProfile::create($this->normalizePayload($request->validated()));

        return redirect()->route('admin.snmp-profiles.index')
            ->with('success', 'Profil SNMP berhasil ditambahkan.');
    }

    public function edit(SnmpProfile $snmpProfile): View
    {
        $this->authorize('update', $snmpProfile);

        return view('admin.snmp-profiles.edit', [
            'profile' => $snmpProfile,
            'versions' => SnmpVersion::cases(),
            'securityLevels' => SnmpSecurityLevel::cases(),
        ]);
    }

    public function update(UpdateSnmpProfileRequest $request, SnmpProfile $snmpProfile): RedirectResponse
    {
        $data = $this->normalizePayload($request->validated(), $snmpProfile);
        $snmpProfile->update($data);

        return redirect()->route('admin.snmp-profiles.index')
            ->with('success', 'Profil SNMP berhasil diperbarui.');
    }

    public function destroy(SnmpProfile $snmpProfile): RedirectResponse
    {
        $this->authorize('delete', $snmpProfile);

        if ($snmpProfile->devices()->exists()) {
            return back()->with('error', 'Profil masih digunakan oleh perangkat.');
        }

        $snmpProfile->delete();

        return redirect()->route('admin.snmp-profiles.index')
            ->with('success', 'Profil SNMP berhasil dihapus.');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function normalizePayload(array $data, ?SnmpProfile $existing = null): array
    {
        if (($data['version'] ?? null) === SnmpVersion::V2c->value) {
            if (empty($data['community']) && $existing) {
                unset($data['community']);
            }

            $data['security_level'] = null;
            $data['username'] = null;
            $data['auth_protocol'] = null;
            $data['auth_passphrase'] = null;
            $data['priv_protocol'] = null;
            $data['priv_passphrase'] = null;
            $data['context_name'] = null;
        }

        if (($data['version'] ?? null) === SnmpVersion::V3->value) {
            $data['community'] = null;

            if (empty($data['auth_passphrase']) && $existing) {
                unset($data['auth_passphrase']);
            }

            if (empty($data['priv_passphrase']) && $existing) {
                unset($data['priv_passphrase']);
            }

            $level = $data['security_level'] ?? null;

            if ($level === SnmpSecurityLevel::NoAuthNoPriv->value) {
                $data['auth_protocol'] = null;
                $data['auth_passphrase'] = null;
                $data['priv_protocol'] = null;
                $data['priv_passphrase'] = null;
            }

            if ($level === SnmpSecurityLevel::AuthNoPriv->value) {
                $data['priv_protocol'] = null;
                $data['priv_passphrase'] = null;
            }
        }

        return $data;
    }
}
