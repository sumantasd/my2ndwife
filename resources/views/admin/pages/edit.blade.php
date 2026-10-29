@extends('layouts.admin')

@section('title', 'Edit Page: ' . $page->title . ' | Admin Portal')
@section('header_title', 'Edit Website Page')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('admin.pages.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-gray-500 hover:text-[#F50070] transition-colors mb-2">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                <span>Back to All Pages</span>
            </a>
            <h2 class="text-xl font-black text-[#25232A]">Edit Page: {{ $page->title }}</h2>
        </div>
        <div>
            <a href="{{ $page->public_url }}" target="_blank" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold transition-colors">
                <span>View Public Page</span>
                <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-xs font-bold space-y-1">
            @foreach($errors->all() as $error)
                <div class="flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 shrink-0"></i>
                    <span>{{ $error }}</span>
                </div>
            @endforeach
        </div>
    @endif

    <form action="{{ route('admin.pages.update', $page) }}" method="POST" 
          class="bg-white rounded-2xl border border-[#F0E6EC] shadow-sm p-6 sm:p-8 space-y-6"
          x-data="{ 
              title: '{{ old('title', addslashes($page->title)) }}', 
              slug: '{{ old('slug', $page->slug) }}'
          }">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Page Title -->
            <div class="sm:col-span-2">
                <label for="title" class="block text-xs font-black text-[#25232A] uppercase tracking-wider mb-1.5">
                    Page Title <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="title" name="title" required x-model="title"
                       class="w-full p-3 rounded-xl border border-[#F0E6EC] text-xs font-bold focus:border-[#F50070] focus:ring-1 focus:ring-[#F50070] focus:outline-none">
            </div>

            <!-- URL Slug -->
            <div>
                <label for="slug" class="block text-xs font-black text-[#25232A] uppercase tracking-wider mb-1.5">
                    URL Slug <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs text-gray-400 font-mono">/page/</span>
                    <input type="text" id="slug" name="slug" x-model="slug" required
                           class="w-full pl-16 pr-3 py-3 rounded-xl border border-[#F0E6EC] text-xs font-mono font-bold focus:border-[#F50070] focus:ring-1 focus:ring-[#F50070] focus:outline-none">
                </div>
                <p class="text-[11px] text-gray-400 mt-1">Changing slug updates the public URL of this page.</p>
            </div>

            <!-- Display Order -->
            <div>
                <label for="display_order" class="block text-xs font-black text-[#25232A] uppercase tracking-wider mb-1.5">
                    Display Order (Sorting)
                </label>
                <input type="number" id="display_order" name="display_order" value="{{ old('display_order', $page->display_order) }}" min="0"
                       class="w-full p-3 rounded-xl border border-[#F0E6EC] text-xs font-bold focus:border-[#F50070] focus:ring-1 focus:ring-[#F50070] focus:outline-none">
            </div>
        </div>

        <!-- Page Content: Visual WYSIWYG Content Editor -->
        <div x-data="visualEditorData()" x-init="initEditor()" class="space-y-2">
            <div class="flex items-center justify-between">
                <label class="block text-xs font-black text-[#25232A] uppercase tracking-wider">
                    Page Content <span class="text-rose-500">*</span>
                </label>
                <div class="flex items-center gap-2">
                    <span class="text-[11px] text-gray-400 font-medium hidden sm:inline" x-show="viewMode === 'visual'">
                        Visual Editor (No HTML Required)
                    </span>
                    <button type="button" @click="toggleViewMode()" 
                            class="px-2.5 py-1 rounded-lg border border-gray-200 hover:bg-gray-100 text-[10px] font-bold text-gray-600 transition-colors cursor-pointer">
                        <span x-text="viewMode === 'visual' ? '</> HTML Source' : '👁️ Visual Editor'"></span>
                    </button>
                </div>
            </div>

            <!-- Editor Container -->
            <div class="rounded-2xl border border-[#F0E6EC] overflow-hidden bg-white shadow-2xs focus-within:border-[#F50070] transition-colors">
                <!-- Visual Toolbar (Shown in Visual Mode) -->
                <div x-show="viewMode === 'visual'" class="p-2 bg-gray-50/90 border-b border-[#F0E6EC] flex flex-wrap items-center gap-1 text-xs select-none">
                    
                    <!-- Text Format Dropdown -->
                    <select @change="applyFormatBlock($event.target.value); $event.target.value = ''" 
                            class="px-2 py-1 rounded-lg border border-gray-200 bg-white font-bold text-gray-700 text-xs focus:outline-none focus:border-[#F50070] cursor-pointer">
                        <option value="">Text Style</option>
                        <option value="p">Normal Paragraph</option>
                        <option value="h2">Heading 2 (H2)</option>
                        <option value="h3">Heading 3 (H3)</option>
                        <option value="blockquote">Quote Block</option>
                    </select>

                    <div class="w-px h-5 bg-gray-300 mx-1"></div>

                    <!-- Bold -->
                    <button type="button" @click="exec('bold')" title="Bold (Ctrl+B)"
                            class="w-7 h-7 rounded-lg flex items-center justify-center font-black text-gray-700 hover:bg-white hover:shadow-2xs active:bg-gray-200 transition-all cursor-pointer">
                        <span>B</span>
                    </button>

                    <!-- Italic -->
                    <button type="button" @click="exec('italic')" title="Italic (Ctrl+I)"
                            class="w-7 h-7 rounded-lg flex items-center justify-center font-bold italic text-gray-700 hover:bg-white hover:shadow-2xs active:bg-gray-200 transition-all cursor-pointer">
                        <span>I</span>
                    </button>

                    <!-- Underline -->
                    <button type="button" @click="exec('underline')" title="Underline (Ctrl+U)"
                            class="w-7 h-7 rounded-lg flex items-center justify-center font-bold underline text-gray-700 hover:bg-white hover:shadow-2xs active:bg-gray-200 transition-all cursor-pointer">
                        <span>U</span>
                    </button>

                    <!-- Strikethrough -->
                    <button type="button" @click="exec('strikeThrough')" title="Strikethrough"
                            class="w-7 h-7 rounded-lg flex items-center justify-center font-bold line-through text-gray-700 hover:bg-white hover:shadow-2xs active:bg-gray-200 transition-all cursor-pointer">
                        <span>S</span>
                    </button>

                    <div class="w-px h-5 bg-gray-300 mx-1"></div>

                    <!-- Bullet List -->
                    <button type="button" @click="exec('insertUnorderedList')" title="Bullet List"
                            class="px-2 h-7 rounded-lg flex items-center gap-1 font-bold text-gray-700 hover:bg-white hover:shadow-2xs active:bg-gray-200 transition-all cursor-pointer text-xs">
                        <span>• Bullet List</span>
                    </button>

                    <!-- Numbered List -->
                    <button type="button" @click="exec('insertOrderedList')" title="Numbered List"
                            class="px-2 h-7 rounded-lg flex items-center gap-1 font-bold text-gray-700 hover:bg-white hover:shadow-2xs active:bg-gray-200 transition-all cursor-pointer text-xs">
                        <span>1. Numbered</span>
                    </button>

                    <div class="w-px h-5 bg-gray-300 mx-1"></div>

                    <!-- Link -->
                    <button type="button" @click="insertLink()" title="Insert Link"
                            class="px-2 h-7 rounded-lg flex items-center gap-1 font-bold text-gray-700 hover:bg-white hover:shadow-2xs active:bg-gray-200 transition-all cursor-pointer text-xs">
                        <i data-lucide="link" class="w-3.5 h-3.5"></i>
                        <span>Link</span>
                    </button>

                    <!-- Clear Format -->
                    <button type="button" @click="exec('removeFormat')" title="Clear Formatting"
                            class="px-2 h-7 rounded-lg flex items-center text-gray-500 hover:bg-white hover:shadow-2xs active:bg-gray-200 transition-all cursor-pointer text-xs">
                        <span>Clear</span>
                    </button>

                    <!-- Undo -->
                    <button type="button" @click="exec('undo')" title="Undo"
                            class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-500 hover:bg-white hover:shadow-2xs active:bg-gray-200 transition-all cursor-pointer ml-auto">
                        <i data-lucide="undo" class="w-3.5 h-3.5"></i>
                    </button>

                    <!-- Redo -->
                    <button type="button" @click="exec('redo')" title="Redo"
                            class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-500 hover:bg-white hover:shadow-2xs active:bg-gray-200 transition-all cursor-pointer">
                        <i data-lucide="redo" class="w-3.5 h-3.5"></i>
                    </button>
                </div>

                <!-- Visual Editable Canvas (Default View) -->
                <div x-show="viewMode === 'visual'"
                     x-ref="visualCanvas"
                     contenteditable="true"
                     @input="syncToTextarea()"
                     @blur="syncToTextarea()"
                     @paste="handlePaste($event)"
                     class="min-h-[320px] p-5 text-sm text-[#25232A] focus:outline-none prose prose-sm max-w-none leading-relaxed bg-white"
                     style="outline: none;"></div>

                <!-- Raw HTML Source Textarea (Hidden by default, shown via toggle) -->
                <textarea id="content" 
                          name="content" 
                          x-ref="htmlTextarea"
                          x-show="viewMode === 'html'"
                          @input="syncToCanvas()"
                          rows="14" 
                          required
                          class="w-full p-5 text-xs font-mono text-gray-800 bg-[#FAF8FB] focus:outline-none border-0 leading-relaxed">{{ old('content', $page->content) }}</textarea>
            </div>

            <p class="text-[11px] text-gray-400">
                You can write plain text like in Word or Google Docs. Use the toolbar buttons for headings, bullet lists, bold text, and links. No HTML coding is required.
            </p>
        </div>

        <!-- SEO Metadata Section -->
        <div class="p-4 rounded-2xl bg-[#FAF8FB] border border-[#F0E6EC] space-y-4">
            <h3 class="text-xs font-black text-[#25232A] uppercase tracking-wider">Search Engine Optimization (SEO)</h3>
            
            <div>
                <label for="meta_title" class="block text-xs font-bold text-gray-700 mb-1">
                    Meta Title Tag (Optional)
                </label>
                <input type="text" id="meta_title" name="meta_title" value="{{ old('meta_title', $page->meta_title) }}"
                       placeholder="e.g. About Us | My 2nd Wife Food Delivery Kolkata"
                       class="w-full p-3 bg-white rounded-xl border border-[#F0E6EC] text-xs font-medium focus:border-[#F50070] focus:outline-none">
            </div>

            <div>
                <label for="meta_description" class="block text-xs font-bold text-gray-700 mb-1">
                    Meta Description (Optional)
                </label>
                <textarea id="meta_description" name="meta_description" rows="3"
                          placeholder="Brief summary of this page for search results"
                          class="w-full p-3 bg-white rounded-xl border border-[#F0E6EC] text-xs font-medium focus:border-[#F50070] focus:outline-none">{{ old('meta_description', $page->meta_description) }}</textarea>
            </div>
        </div>

        <!-- Publish Toggle -->
        <div class="flex items-center gap-3">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_published" value="1" {{ old('is_published', $page->is_published) ? 'checked' : '' }}
                       class="w-4 h-4 rounded text-[#F50070] focus:ring-[#F50070] border-gray-300">
                <span class="text-xs font-bold text-[#25232A]">Published (visible to website visitors)</span>
            </label>
        </div>

        <!-- Submit Buttons -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
            <a href="{{ route('admin.pages.index') }}" 
               class="px-5 py-2.5 rounded-xl border border-gray-200 text-xs font-bold text-gray-600 hover:bg-gray-50 transition-colors">
                Cancel
            </a>
            <button type="submit" 
                    class="px-6 py-2.5 rounded-xl bg-[#F50070] hover:bg-[#D40060] text-white text-xs font-black shadow-md shadow-pink-500/20 transition-all active:scale-95 cursor-pointer flex items-center gap-2">
                <i data-lucide="save" class="w-4 h-4"></i>
                <span>Save Changes</span>
            </button>
        </div>
    </form>
</div>

<script>
function visualEditorData() {
    return {
        viewMode: 'visual',
        initEditor() {
            const raw = this.$refs.htmlTextarea ? this.$refs.htmlTextarea.value : '';
            if (this.$refs.visualCanvas) {
                this.$refs.visualCanvas.innerHTML = raw;
            }
            if (typeof lucide !== 'undefined' && lucide.createIcons) {
                this.$nextTick(() => lucide.createIcons());
            }
        },
        toggleViewMode() {
            if (this.viewMode === 'visual') {
                this.syncToTextarea();
                this.viewMode = 'html';
            } else {
                this.syncToCanvas();
                this.viewMode = 'visual';
            }
            if (typeof lucide !== 'undefined' && lucide.createIcons) {
                this.$nextTick(() => lucide.createIcons());
            }
        },
        exec(command, value = null) {
            document.execCommand(command, false, value);
            this.syncToTextarea();
            if (this.$refs.visualCanvas) {
                this.$refs.visualCanvas.focus();
            }
        },
        applyFormatBlock(tag) {
            if (!tag) return;
            document.execCommand('formatBlock', false, tag);
            this.syncToTextarea();
            if (this.$refs.visualCanvas) {
                this.$refs.visualCanvas.focus();
            }
        },
        insertLink() {
            const currentSelection = window.getSelection().toString();
            const url = prompt('Enter website link URL (e.g. https://... or /menu):', 'https://');
            if (url && url.trim() !== '' && url !== 'https://') {
                if (!currentSelection) {
                    document.execCommand('insertHTML', false, `<a href="${url}" target="_blank" rel="noopener noreferrer">${url}</a>`);
                } else {
                    document.execCommand('createLink', false, url);
                }
                this.syncToTextarea();
            }
            if (this.$refs.visualCanvas) {
                this.$refs.visualCanvas.focus();
            }
        },
        syncToTextarea() {
            if (this.$refs.visualCanvas && this.$refs.htmlTextarea) {
                this.$refs.htmlTextarea.value = this.$refs.visualCanvas.innerHTML;
            }
        },
        syncToCanvas() {
            if (this.$refs.visualCanvas && this.$refs.htmlTextarea) {
                this.$refs.visualCanvas.innerHTML = this.$refs.htmlTextarea.value;
            }
        },
        handlePaste(e) {
            this.$nextTick(() => {
                this.syncToTextarea();
            });
        }
    };
}
</script>
@endsection
