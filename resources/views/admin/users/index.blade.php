@extends('layouts.app')

@section('title', 'Pengguna Sistem')
@section('breadcrumb', 'Pengaturan / Pengguna')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Manajemen Pengguna Sistem</h2>
            <p class="text-xs text-slate-500">Akun pengelola: Administrator dan Petugas Loket</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm shadow-sm inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Pengguna
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 text-xs font-semibold uppercase tracking-wider">
                    <th class="py-3.5 px-6">Nama Pengguna</th>
                    <th class="py-3.5 px-6">Email</th>
                    <th class="py-3.5 px-6">Peran (Role)</th>
                    <th class="py-3.5 px-6">Status</th>
                    <th class="py-3.5 px-6 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($users as $user)
                    <tr class="hover:bg-slate-50/50">
                        <td class="py-4 px-6 font-semibold text-slate-800">{{ $user->name }}</td>
                        <td class="py-4 px-6 text-slate-600 text-xs">{{ $user->email }}</td>
                        <td class="py-4 px-6">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold uppercase {{ $user->role === 'admin' ? 'bg-purple-50 text-purple-700' : 'bg-emerald-50 text-emerald-700' }}">
                                {{ $user->role }}
                            </span>
                        </td>
                        <td class="py-4 px-6">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $user->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="py-4 px-6 text-right">
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('admin.users.edit', $user) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-sky-600 hover:bg-sky-50" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>

                                <button type="button" 
                                        @click="$dispatch('open-confirm-modal', {
                                            action: '{{ route('admin.users.reset-password', $user) }}',
                                            method: 'POST',
                                            title: 'Reset Password Pengguna',
                                            message: 'Reset kata sandi akun {{ $user->name }} menjadi \'password123\'?',
                                            confirmText: 'Reset Password',
                                            confirmClass: 'bg-amber-600 hover:bg-amber-700 text-white'
                                        })"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50" 
                                        title="Reset Password">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                </button>

                                @if($user->id !== auth()->id())
                                    <button type="button" 
                                            @click="$dispatch('open-confirm-modal', {
                                                action: '{{ route('admin.users.toggle', $user) }}',
                                                method: 'PATCH',
                                                title: 'Ubah Status Akun',
                                                message: 'Apakah Anda yakin ingin {{ $user->is_active ? 'menonaktifkan' : 'mengaktifkan' }} akun {{ $user->name }}?',
                                                confirmText: '{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}',
                                                confirmClass: '{{ $user->is_active ? 'bg-rose-600 hover:bg-rose-700 text-white' : 'bg-emerald-600 hover:bg-emerald-700 text-white' }}'
                                            })"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50" 
                                            title="Aktif/Nonaktif">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

