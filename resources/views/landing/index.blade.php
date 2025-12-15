@extends('landing.layout')

@section('content')
    @include('landing.partials.hero')

    @include('landing.partials.features')

    @include('landing.partials.pricing')

    @include('landing.partials.cta')

        <!-- About Section -->
        <section id="about" class="container mx-auto px-6 py-20">
            <h2 class="text-4xl font-bold text-center text-gray-900 mb-12">من نحن</h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto text-center">
                نحن فريق متخصص في تطوير حلول إدارة الموارد البشرية التي تساعد الشركات على إدارة موظفيها بكفاءة وفعالية.
            </p>
        </section>

        <!-- Contact Section -->
        <section id="contact" class="bg-gray-50 py-20">
            <div class="container mx-auto px-6">
                <h2 class="text-4xl font-bold text-center text-gray-900 mb-12">اتصل بنا</h2>
                <div class="max-w-md mx-auto">
                    <p class="text-center text-gray-600 mb-6">
                        للاستفسارات والدعم الفني، يرجى التواصل معنا
                    </p>
                    <div class="text-center">
                        <a href="mailto:support@example.com" class="text-blue-600 hover:text-blue-700">
                            support@example.com
                        </a>
                    </div>
                </div>
            </div>
        </section>
@endsection

