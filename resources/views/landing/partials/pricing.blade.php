@php
$plans = [
    [
        'name' => 'الخطة الأساسية',
        'price' => '99',
        'period' => 'شهرياً',
        'description' => 'مثالية للشركات الصغيرة والناشئة',
        'features' => [
            'حتى 25 موظف',
            'إدارة الحضور والغياب',
            'إدارة بيانات الموظفين',
            'تقارير أساسية',
            'دعم فني عبر البريد',
            'تخزين 5 جيجابايت'
        ],
        'popular' => false,
        'color' => 'gray'
    ],
    [
        'name' => 'الخطة الاحترافية',
        'price' => '249',
        'period' => 'شهرياً',
        'description' => 'الأنسب للشركات المتوسطة',
        'features' => [
            'حتى 100 موظف',
            'جميع مميزات الخطة الأساسية',
            'إدارة الرواتب والمكافآت',
            'تقييم الأداء',
            'تقارير متقدمة',
            'دعم فني أولوية',
            'تخزين 50 جيجابايت',
            'تطبيق الجوال'
        ],
        'popular' => true,
        'color' => 'blue'
    ],
    [
        'name' => 'الخطة المؤسسية',
        'price' => '599',
        'period' => 'شهرياً',
        'description' => 'حل شامل للمؤسسات الكبيرة',
        'features' => [
            'موظفين غير محدودين',
            'جميع مميزات الخطة الاحترافية',
            'تخصيص كامل للنظام',
            'API متقدم',
            'مدير حساب خاص',
            'دعم فني 24/7',
            'تخزين غير محدود',
            'تدريب مخصص للفريق',
            'تكامل مع الأنظمة الأخرى'
        ],
        'popular' => false,
        'color' => 'purple'
    ]
];
@endphp

<section id="pricing" class="py-24 bg-gradient-to-b from-gray-50 to-white">
    <div class="container mx-auto px-6">
        <div class="text-center mb-16">
            <h2 class="text-4xl mb-4 text-gray-900">اختر الخطة المناسبة لك</h2>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                خطط مرنة تناسب جميع أحجام الشركات مع إمكانية الترقية في أي وقت
            </p>
        </div>

        <div class="grid md:grid-cols-3 gap-8 max-w-7xl mx-auto">
            @foreach($plans as $index => $plan)
                <div 
                    class="relative p-8 bg-white border rounded-lg transition-all pricing-card {{ $plan['popular'] ? 'border-blue-500 border-2 shadow-2xl scale-105' : 'border-gray-200 hover:shadow-lg' }}"
                    data-plan-name="{{ $plan['name'] }}"
                    data-popular="{{ $plan['popular'] ? 'true' : 'false' }}"
                >
                    @if($plan['popular'])
                        <span class="absolute -top-3 right-1/2 translate-x-1/2 bg-blue-600 text-white px-4 py-1 rounded-full text-sm">
                            الأكثر شعبية
                        </span>
                    @endif
                    
                    <div class="text-center mb-8">
                        <h3 class="text-2xl mb-3 text-gray-900">{{ $plan['name'] }}</h3>
                        <p class="text-gray-600 mb-6">{{ $plan['description'] }}</p>
                        
                        <div class="mb-4">
                            <span class="text-5xl text-gray-900">{{ $plan['price'] }}</span>
                            <span class="text-gray-600 mr-2">ريال</span>
                        </div>
                        <div class="text-gray-500">{{ $plan['period'] }}</div>
                    </div>

                    <ul class="space-y-4 mb-8">
                        @foreach($plan['features'] as $feature)
                            <li class="flex items-start gap-3">
                                <svg class="size-5 mt-0.5 flex-shrink-0 {{ $plan['popular'] ? 'text-blue-600' : 'text-green-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-700">{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <button 
                        class="w-full px-4 py-2 rounded-lg transition-colors pricing-button {{ $plan['popular'] ? 'bg-blue-600 hover:bg-blue-700 text-white' : 'bg-gray-100 hover:bg-gray-200 text-gray-900' }}"
                        data-plan-name="{{ $plan['name'] }}"
                        onclick="selectPlan('{{ $plan['name'] }}')"
                    >
                        <span class="button-text">اختر هذه الخطة</span>
                    </button>
                </div>
            @endforeach
        </div>

        <div class="text-center mt-12 text-gray-600">
            <p>جميع الخطط تشمل تجربة مجانية لمدة 14 يوم • بدون بطاقة ائتمانية</p>
        </div>
    </div>
</section>

@push('scripts')
<script>
    function selectPlan(planName) {
        // Remove selected state from all cards
        document.querySelectorAll('.pricing-card').forEach(card => {
            const isPopular = card.dataset.popular === 'true';
            if (!isPopular) {
                card.classList.remove('border-blue-300', 'border-2', 'shadow-lg');
                card.classList.add('border-gray-200');
            }
        });

        // Remove selected state from all buttons
        document.querySelectorAll('.pricing-button').forEach(button => {
            const isPopular = button.closest('.pricing-card').dataset.popular === 'true';
            if (!isPopular) {
                button.classList.remove('bg-blue-600', 'hover:bg-blue-700', 'text-white');
                button.classList.add('bg-gray-100', 'hover:bg-gray-200', 'text-gray-900');
                button.querySelector('.button-text').textContent = 'اختر هذه الخطة';
            }
        });

        // Add selected state to clicked card
        const clickedCard = document.querySelector(`[data-plan-name="${planName}"]`);
        const clickedButton = clickedCard.querySelector('.pricing-button');
        const isPopular = clickedCard.dataset.popular === 'true';

        if (!isPopular) {
            clickedCard.classList.add('border-blue-300', 'border-2', 'shadow-lg');
            clickedCard.classList.remove('border-gray-200');
            
            clickedButton.classList.add('bg-blue-600', 'hover:bg-blue-700', 'text-white');
            clickedButton.classList.remove('bg-gray-100', 'hover:bg-gray-200', 'text-gray-900');
            clickedButton.querySelector('.button-text').textContent = '✓ تم الاختيار';
        }

        // Optional: Redirect to registration with plan selection
        // window.location.href = '{{ route("register") }}?plan=' + encodeURIComponent(planName);
    }
</script>
@endpush

