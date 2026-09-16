@extends('layouts.admin')
@section('title', 'Site Settings — ChairGhor')
@section('content')
@php
    $selectedSection = old('_section', request('section', request('saved')));
    $selectedSection = array_key_exists($selectedSection ?? '', $sections) ? $selectedSection : array_key_first($sections);
    $fields = $sections[$selectedSection];
    $section = $selectedSection;
    $imageFields = array_filter($fields, fn ($field) => $field['type'] === 'image');
    $textFields = array_filter($fields, fn ($field) => $field['type'] !== 'image');
@endphp
<div class="site-editor" data-site-editor>
    <header class="site-editor-heading">
        <div><span class="site-editor-kicker">ওয়েবসাইট ম্যানেজমেন্ট</span><h1>সাইটের কনটেন্ট</h1><p>আপনার ওয়েবসাইটের লেখা ও ছবি নিজের মতো সাজান।</p></div>
        <a class="btn btn-admin-light" href="{{ route('home') }}" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i> ওয়েবসাইট দেখুন</a>
    </header>

    <div class="site-editor-toolbar">
        <form method="get" action="{{ route('admin.settings.site') }}" class="site-page-picker">
            <label for="site-page"><span class="site-step">১</span> কোন পেজ পরিবর্তন করবেন?</label>
            <div class="site-page-select"><select id="site-page" name="page" class="form-select" data-site-page-select>@foreach($pages as $key => $label)<option value="{{ $key }}" @selected($page === $key)>{{ $label }}</option>@endforeach</select><button class="btn btn-admin-light" type="submit">খুলুন <i class="bi bi-arrow-right"></i></button></div>
        </form>
        <div class="site-editor-tip"><i class="bi bi-info-circle"></i><span>পেজ বাছুন, সেকশন খুলুন, তারপর পরিবর্তন সেভ করুন।<small>সেভ করার পর পরিবর্তন ওয়েবসাইটে দেখা যাবে।</small></span></div>
    </div>

    <div class="site-editor-layout">
        <aside class="site-section-panel">
            <div class="site-section-panel-heading"><span class="site-step">২</span><div><h2>{{ $pages[$page] }}</h2><p>{{ count($sections) }}টি সেকশন · একটি বেছে নিন</p></div></div>
            <nav class="site-section-list" aria-label="সেকশন নির্বাচন">
                @foreach($sections as $sectionKey => $sectionFields)
                    @php
                        $hasImage = count(array_filter($sectionFields, fn ($field) => $field['type'] === 'image')) > 0;
                    @endphp
                    <a class="{{ $sectionKey === $selectedSection ? 'is-active' : '' }}" href="{{ route('admin.settings.site', ['page' => $page, 'section' => $sectionKey]) }}" @if($sectionKey === $selectedSection) aria-current="true" @endif>
                        <span class="site-section-icon"><i class="bi {{ $hasImage ? 'bi-image' : 'bi-card-text' }}"></i></span><span>{{ \App\Services\SiteContent::sectionLabel($sectionKey) }}<small>{{ count($sectionFields) }}টি তথ্য {{ $hasImage ? '· ছবি আছে' : '' }}</small></span><i class="bi bi-chevron-right"></i>
                    </a>
                @endforeach
            </nav>
            <div class="site-related-settings"><i class="bi bi-sliders2"></i><span>অন্য কিছু পরিবর্তন করবেন?<a href="{{ route('admin.settings.general') }}">ফোন, ইমেইল ও ঠিকানা <i class="bi bi-arrow-up-right"></i></a><a href="{{ route('admin.products.index') }}">প্রোডাক্টের তথ্য <i class="bi bi-arrow-up-right"></i></a></span></div>
        </aside>

        <details class="site-content-panel" id="section-{{ $section }}" open>
            <summary class="site-content-heading"><div><span class="site-editor-kicker">{{ $pages[$page] }}</span><h2>{{ \App\Services\SiteContent::sectionLabel($section) }}</h2><p>নিচের ঘরগুলোতে প্রয়োজনীয় পরিবর্তন করুন।</p></div><span class="site-edit-badge"><i class="bi bi-pencil-square"></i> এডিট করুন</span></summary>
            <form method="post" action="{{ route('admin.settings.site.update', [$page, $section]) }}" enctype="multipart/form-data" data-site-content-form>
                @csrf @method('PUT')
                <input type="hidden" name="_section" value="{{ $section }}">
                <div class="site-content-body {{ count($imageFields) ? 'has-images' : '' }}">
                    <div class="site-copy-fields">
                        @if($page === 'contact' && $section === 'contact_details')<p class="text-muted small">ফোন, ইমেইল ও ঠিকানা এখান থেকেই বদলাতে পারবেন। ডেমো তথ্যের জায়গায় আপনার ব্যবসার আসল তথ্য দিন।</p>@endif
                        <h3 class="site-field-group-heading"><i class="bi bi-fonts"></i> লেখা ও লিংক</h3>
                        @foreach($textFields as $key => $field)
                            @php
                                $value = old('_section') === $section ? old('content.'.$key, $siteContent->text($page, $section, $key)) : $siteContent->text($page, $section, $key);
                                $isLong = mb_strlen($field['default']) > 85 || in_array($key, ['body', 'help']);
                            @endphp
                            <div class="site-field">
                                <label for="{{ $section }}-{{ $key }}">{{ $field['label'] }}</label>
                                @if($isLong)
                                    <textarea class="form-control" id="{{ $section }}-{{ $key }}" name="content[{{ $key }}]" rows="4" maxlength="10000">{{ $value }}</textarea>
                                @else
                                    <input class="form-control" id="{{ $section }}-{{ $key }}" name="content[{{ $key }}]" value="{{ $value }}" maxlength="{{ $field['type'] === 'link' ? 2048 : 10000 }}" @if($field['type'] === 'link') dir="ltr" @endif>
                                @endif
                                @if($field['type'] === 'link')<small>বাটনে ক্লিক করলে এই ঠিকানায় যাবে। যেমন: /shop</small>@endif
                                @error('content.'.$key)<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                        @endforeach
                    </div>
                    @if(count($imageFields))
                        <aside class="site-image-fields">
                            <h3 class="site-field-group-heading"><i class="bi bi-image"></i> সেকশনের ছবি</h3>
                            @foreach($imageFields as $key => $field)
                                @php
                                    $value = old('_section') === $section ? old('content.'.$key, $siteContent->text($page, $section, $key)) : $siteContent->text($page, $section, $key);
                                @endphp
                                <p class="form-label">{{ $field['label'] }}</p>
                                <div class="site-image-card" data-site-image>
                                    <div class="site-image-preview"><img @if($value) src="{{ $value }}" @else hidden @endif alt="{{ $field['label'] }}" data-site-image-preview><span>ছবির প্রিভিউ</span></div>
                                    <div class="site-image-controls">
                                        <label class="site-upload-button" for="upload-{{ $key }}"><i class="bi bi-cloud-arrow-up"></i> নতুন ছবি বেছে নিন</label>
                                        <input class="visually-hidden" id="upload-{{ $key }}" type="file" name="uploads[{{ $key }}]" accept="image/jpeg,image/png,image/webp" data-site-image-upload>
                                        <small data-site-image-name>JPG, PNG বা WebP · সর্বোচ্চ ৫ MB</small>
                                        @error('uploads.'.$key)<div class="text-danger small">{{ $message }}</div>@enderror
                                        @if($field['optional'] ?? false)<small>লোগো সরাতে ছবির লিংক খালি করে সেভ করুন। লোগো না থাকলে সাইটের নাম দেখাবে।</small>@endif
                                        <div class="site-image-divider"><span>অথবা ছবির লিংক দিন</span></div>
                                        <label class="visually-hidden" for="{{ $section }}-{{ $key }}">ছবির লিংক</label>
                                        <input class="form-control" id="{{ $section }}-{{ $key }}" name="content[{{ $key }}]" value="{{ $value }}" maxlength="2048" dir="ltr" data-site-image-url>
                                        @error('content.'.$key)<div class="text-danger small">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            @endforeach
                            <p class="site-image-hint"><i class="bi bi-lightbulb"></i> পরিষ্কার ও ভালো মানের ছবি ব্যবহার করুন। নতুন ছবি বাছলে উপরে প্রিভিউ দেখতে পাবেন।</p>
                        </aside>
                    @endif
                </div>
                <footer class="site-save-bar"><span data-site-save-status role="status"><i class="bi bi-check-circle"></i> এডিট করার জন্য প্রস্তুত</span><button type="submit" class="btn btn-admin-primary"><i class="bi bi-check2-circle"></i> পরিবর্তন সেভ করুন</button></footer>
            </form>
        </details>
    </div>
</div>
@endsection
