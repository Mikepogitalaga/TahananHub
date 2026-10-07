@extends('layouts.admin')

@section('page_title', 'Platform Settings')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-900">Platform Configuration</h2>
            <p class="text-sm text-gray-500">Configure global parameters and security thresholds for the E-Boarding SaaS.</p>
        </div>

        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Platform Name</label>
                <input type="text" name="platform_name" value="{{ $settings['platform_name'] ?? 'E-Boarding' }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Support Email</label>
                    <input type="email" name="support_email" value="{{ $settings['support_email'] ?? 'support@eboarding.test' }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Currency Symbol</label>
                    <input type="text" name="currency_symbol" value="{{ $settings['currency_symbol'] ?? '₱' }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="border-t border-gray-100 pt-4">
                <h3 class="text-sm font-bold text-gray-800 mb-2">Privacy & Security Policies</h3>

                <div class="space-y-3">
                    <label class="flex items-start gap-3 cursor-pointer text-sm">
                        <input type="checkbox" name="strict_privacy_enforcement" value="1"
                               {{ ($settings['strict_privacy_enforcement'] ?? '1') === '1' ? 'checked' : '' }}
                               class="mt-1 rounded text-blue-600 focus:ring-blue-500">
                        <div>
                            <span class="font-semibold text-gray-900">Strict Privacy Protocol (Recommended)</span>
                            <span class="block text-xs text-gray-500">Enforce zero-trust database boundary preventing Super Admin direct access without approved session.</span>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 cursor-pointer text-sm">
                        <input type="checkbox" name="auto_expire_sessions" value="1"
                               {{ ($settings['auto_expire_sessions'] ?? '1') === '1' ? 'checked' : '' }}
                               class="mt-1 rounded text-blue-600 focus:ring-blue-500">
                        <div>
                            <span class="font-semibold text-gray-900">Automatic Session Expiration</span>
                            <span class="block text-xs text-gray-500">Automatically deactivate approved access sessions once their duration finishes.</span>
                        </div>
                    </label>
                </div>
            </div>

            <div class="pt-4 flex justify-end border-t border-gray-100">
                <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold shadow-sm">
                    Save Platform Settings
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

