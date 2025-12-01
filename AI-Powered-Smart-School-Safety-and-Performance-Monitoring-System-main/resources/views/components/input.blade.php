<div class="mt-2">
    @if ($title != '')
    <small class="text-xs">{{ $title }} @if ($isRequired)
        <span class="text-danger text-md">*</span>
        @endif
    </small>
    @endif


    <div class="input-group input-group-outline my-1">
        @if ($type === 'select')
        <select name="{{ $name }}" id="{{ $id }}" class="form-control {{ $class ?? '' }} @error($name) is-invalid @enderror"
            {{ $attr }} @if ($isRequired) required @endif>
            <option value="">{{ $placeholder ?? 'Select an option' }}</option>
            @foreach ($options as $key => $label)
            <option value="{{ $key }}" @selected($value==$key)>
                {{ $label }}
            </option>
            @endforeach
        </select>
        @elseif ($type === 'textarea')
        <textarea name="{{ $name }}" id="{{ $name }}"
            class="form-control {{ $class ?? '' }} @error($name) is-invalid @enderror" {{ $attr }}
            @if ($isRequired) required @endif placeholder="{{ $placeholder }}">{{ old($name, $value) }}</textarea>
        @elseif ($type === 'file')
        <input type="file" name="{{ $name }}" id="{{ $name }}"
            class="form-control {{ $class ?? '' }} @error($name) is-invalid @enderror" {{ $attr }}
            @if ($isRequired) required @endif onchange="previewFile{{ ucfirst($name) }}(event)"
            accept="{{ $accept ?? 'image/*' }}">

        @if ($showPreview ?? false)
        <div class="file-preview-container mt-2" id="{{ $name }}-preview-container">
            @if ($value && $type === 'file')
            <div class="current-file-preview text-center">
                @if (str_contains($accept ?? 'image/*', 'image'))
                <img id="{{ $name }}-preview" src="{{ asset('storage/' . $value) }}"
                    alt="{{ $title }} Preview"
                    style="max-height: 120px; max-width: 200px; border-radius: 8px; object-fit: contain; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                @else
                <div id="{{ $name }}-preview" class="file-info">
                    <i class="material-symbols-rounded">description</i>
                    <p class="mb-0">{{ basename($value) }}</p>
                </div>
                @endif
            </div>
            @else
            <div id="{{ $name }}-preview" class="no-file-placeholder text-center"
                style="width: 120px; height: 120px; background: linear-gradient(135deg, #f8f9fa, #e9ecef); border-radius: 8px; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #6c757d;">
                <i class="material-symbols-rounded" style="font-size: 2rem; margin-bottom: 0.25rem;">
                    {{ str_contains($accept ?? 'image/*', 'image') ? 'image' : 'description' }}
                </i>
                <p class="mb-0" style="font-size: 0.75rem;">No file</p>
            </div>
            @endif
        </div>

        <script>
            function previewFile {
                {
                    ucfirst($name)
                }
            }(event) {
                const file = event.target.files[0];
                const preview = document.getElementById('{{ $name }}-preview');
                const maxSize = {
                    {
                        $maxSize ?? 2048
                    }
                }; // KB
                const allowedTypes = '{{ $accept ?? '
                image
                /*' }}'.split(',').map(type => type.trim());

                                        if (file) {
                                            // Check file size
                                            if (file.size > maxSize * 1024) {
                                                alert(`File size must be less than ${maxSize}KB`);
                                                event.target.value = '';
                                                return;
                                            }

                                            // Check file type for images
                                            if ('{{ $accept ?? 'image/*' }}'.includes('image') && !file.type.startsWith('image/')) {
                                                alert('Please select a valid image file');
                                                event.target.value = '';
                                                return;
                                            }

                                            const reader = new FileReader();
                                            reader.onload = function(e) {
                                                if ('{{ $accept ?? 'image/*' }}'.includes('image')) {
                                                    preview.innerHTML = '';
                                                    preview.className = 'current-file-preview';

                                                    const img = document.createElement('img');
                                                    img.src = e.target.result;
                                                    img.alt = '{{ $title }} Preview';
                                                    img.style.cssText =
                                                        'max-height: 120px; max-width: 200px; border-radius: 8px; object-fit: contain; box-shadow: 0 2px 8px rgba(0,0,0,0.1);';
                                                    preview.appendChild(img);
                                                } else {
                                                    preview.innerHTML =
                                                        `<i class="material-symbols-rounded">description</i><p class="mb-0">${file.name}</p>`;
                                                }

                                                // Trigger callback if exists (for dynamic updates like sidebar logo)
                                                if (typeof window.onFilePreview{{ ucfirst($name) }} === 'function') {
                                                    window.onFilePreview{{ ucfirst($name) }}(e.target.result, file);
                                                }
                                            };
                                            reader.readAsDataURL(file);
                                        }
                                    }
        </script>
        @endif
        @else
        <input value="{{ $value }}" type="{{ $type }}" name="{{ $name }}"
            id="{{ $name }}" class="form-control {{ $class ?? '' }} @error($name) is-invalid @enderror"
            {{ $attr }} @if ($isRequired) required @endif
            @if ($type==='checkbox' || $type==='radio' ) value="1"
            @checked(old($name)) @endif>

        @if ($type === 'checkbox' || $type === 'radio')
        <label class="form-check-label" for="{{ $name }}">
            {{ $title ?? ucwords(str_replace('_', ' ', $name)) }}
        </label>
        @endif
        @endif
    </div>

    @include('admin.layouts.form-error', ['input' => $name])

    @if ($placeholder && $type !== 'checkbox' && $type !== 'radio' && $type !== 'select')
    <small class="text-primary ms-2">{{ $placeholder }}</small>
    @endif
</div>