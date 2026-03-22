@if(session('success'))
    <div x-data="{ show: true }" 
         x-show="show"
         x-init="setTimeout(() => show = false, 5000)"
         x-transition
         class="mb-4 bg-green-50 border border-green-200 rounded-xl p-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <i class="fas fa-check-circle text-green-500 text-xl"></i>
            <p class="text-green-700">{{ session('success') }}</p>
        </div>
        <button @click="show = false" class="text-green-500 hover:text-green-700">
            <i class="fas fa-times"></i>
        </button>
    </div>
@endif

@if(session('error'))
    <div x-data="{ show: true }" 
         x-show="show"
         x-init="setTimeout(() => show = false, 5000)"
         x-transition
         class="mb-4 bg-red-50 border border-red-200 rounded-xl p-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <i class="fas fa-exclamation-circle text-red-500 text-xl"></i>
            <p class="text-red-700">{{ session('error') }}</p>
        </div>
        <button @click="show = false" class="text-red-500 hover:text-red-700">
            <i class="fas fa-times"></i>
        </button>
    </div>
@endif

@if(session('warning'))
    <div x-data="{ show: true }" 
         x-show="show"
         x-init="setTimeout(() => show = false, 5000)"
         x-transition
         class="mb-4 bg-yellow-50 border border-yellow-200 rounded-xl p-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <i class="fas fa-exclamation-triangle text-yellow-500 text-xl"></i>
            <p class="text-yellow-700">{{ session('warning') }}</p>
        </div>
        <button @click="show = false" class="text-yellow-500 hover:text-yellow-700">
            <i class="fas fa-times"></i>
        </button>
    </div>
@endif

@if(session('info'))
    <div x-data="{ show: true }" 
         x-show="show"
         x-init="setTimeout(() => show = false, 5000)"
         x-transition
         class="mb-4 bg-blue-50 border border-blue-200 rounded-xl p-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <i class="fas fa-info-circle text-blue-500 text-xl"></i>
            <p class="text-blue-700">{{ session('info') }}</p>
        </div>
        <button @click="show = false" class="text-blue-500 hover:text-blue-700">
            <i class="fas fa-times"></i>
        </button>
    </div>
@endif

@if($errors->any())
    <div x-data="{ show: true }" 
         x-show="show"
         class="mb-4 bg-red-50 border border-red-200 rounded-xl p-4">
        <div class="flex items-start gap-3">
            <i class="fas fa-exclamation-circle text-red-500 text-xl mt-0.5"></i>
            <div class="flex-1">
                <p class="text-red-700 font-medium mb-2">Por favor corrige los siguientes errores:</p>
                <ul class="list-disc list-inside text-red-600 text-sm space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button @click="show = false" class="text-red-500 hover:text-red-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
@endif
