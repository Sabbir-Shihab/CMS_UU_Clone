<script src="{{ asset('vendor/tinymce/tinymce.min.js') }}"></script>
<script>
  (function () {
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const selector = @json($selector ?? '#post-body');
    const height = {{ (int) ($height ?? 460) }};
    const uploadUrl = @json(route('admin.uploads.store'));

    function uploadImageFile(file) {
      return new Promise((resolve, reject) => {
        if (!file || !String(file.type || '').startsWith('image/')) {
          reject('Please choose an image file (JPG, PNG, or WebP).');
          return;
        }
        if (file.size > 10 * 1024 * 1024) {
          reject('Image is too large. Max size is 10 MB.');
          return;
        }
        const form = new FormData();
        form.append('file', file, file.name || 'photo.jpg');
        form.append('folder', 'pages');
        fetch(uploadUrl, {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
          body: form
        }).then(async (res) => {
          const data = await res.json().catch(() => ({}));
          if (!res.ok || !data.location) {
            reject(data.message || 'Upload failed. Try another photo.');
            return;
          }
          resolve(data.location);
        }).catch(() => reject('Upload failed. Check your connection and try again.'));
      });
    }

    function pickImageFile() {
      return new Promise((resolve, reject) => {
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = 'image/jpeg,image/png,image/webp,image/gif';
        input.style.display = 'none';
        document.body.appendChild(input);
        input.addEventListener('change', () => {
          const file = input.files && input.files[0];
          document.body.removeChild(input);
          if (!file) {
            reject('cancelled');
            return;
          }
          resolve(file);
        });
        input.click();
      });
    }

    function insertPhoto(editor, url) {
      editor.insertContent(
        '<p><img src="' + url.replace(/"/g, '&quot;') + '" alt="" style="max-width:100%;height:auto;"></p>'
      );
      editor.focus();
    }

    async function handleChosenFile(file, editorId, box) {
      const editor = window.tinymce ? tinymce.get(editorId) : null;
      if (!editor) {
        throw 'Open the document box first, then select a photo.';
      }
      const status = box?.querySelector('[data-photo-status]');
      const preview = box?.querySelector('[data-photo-preview]');
      const previewImg = preview?.querySelector('img');
      if (status) status.textContent = 'Uploading photo…';
      if (preview && previewImg && file) {
        const localUrl = URL.createObjectURL(file);
        previewImg.src = localUrl;
        preview.classList.add('is-on');
      }
      const url = await uploadImageFile(file);
      insertPhoto(editor, url);
      if (status) status.textContent = 'Photo added under this header.';
      setTimeout(() => { if (status) status.textContent = ''; }, 3500);
      return url;
    }

    function bindChoosePhotoButtons() {
      document.querySelectorAll('[data-choose-photo]').forEach((btn) => {
        if (btn.dataset.bound === '1') return;
        btn.dataset.bound = '1';
        btn.addEventListener('click', async () => {
          const target = btn.getAttribute('data-choose-photo');
          const box = btn.closest('[data-photo-box]') || btn.parentElement;
          try {
            btn.disabled = true;
            const status = box?.querySelector('[data-photo-status]');
            if (status) status.textContent = 'Choose a photo from your computer…';
            const file = await pickImageFile();
            await handleChosenFile(file, target, box);
          } catch (err) {
            const status = box?.querySelector('[data-photo-status]');
            if (err !== 'cancelled' && status) status.textContent = String(err);
            if (err !== 'cancelled' && !status) alert(String(err));
          } finally {
            btn.disabled = false;
          }
        });
      });

      document.querySelectorAll('[data-choose-photo-input]').forEach((input) => {
        if (input.dataset.bound === '1') return;
        input.dataset.bound = '1';
        input.addEventListener('change', async () => {
          const file = input.files && input.files[0];
          if (!file) return;
          const target = input.getAttribute('data-choose-photo-input');
          const box = input.closest('[data-photo-box]') || input.parentElement;
          const status = box?.querySelector('[data-photo-status]');
          try {
            input.disabled = true;
            await handleChosenFile(file, target, box);
          } catch (err) {
            if (status) status.textContent = String(err);
            else alert(String(err));
          } finally {
            input.disabled = false;
            input.value = '';
          }
        });
      });
    }

    tinymce.init({
      selector,
      base_url: @json(asset('vendor/tinymce')),
      suffix: '.min',
      height,
      menubar: false,
      branding: false,
      statusbar: true,
      resize: true,
      plugins: 'link lists image table autoresize code',
      toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright | bullist numlist | table | link | choosephoto | removeformat',
      block_formats: 'Paragraph=p; Heading=h2; Small heading=h3',
      placeholder: 'Start writing your content here…',
      convert_urls: false,
      relative_urls: false,
      verify_html: false,
      valid_elements: '*[*]',
      extended_valid_elements: '*[*]',
      automatic_uploads: true,
      images_reuse_filename: true,
      file_picker_types: 'image',
      content_style: `
        body {
          font-family: "Source Sans 3", "Segoe UI", sans-serif;
          font-size: 15px;
          line-height: 1.65;
          color: #0c1b33;
          padding: 16px 20px;
          max-width: 100%;
        }
        h2 { font-size: 1.35rem; margin: 1.2em 0 .5em; letter-spacing: -.02em; }
        h3 { font-size: 1.1rem; margin: 1em 0 .4em; }
        p { margin: 0 0 .85em; }
        img { max-width: 100%; height: auto; border-radius: 6px; }
        a { color: #0b4aa2; }
        table { border-collapse: collapse; width: 100%; table-layout: auto; margin: 1em 0; }
        thead th { background: #eff6ff; color: #0c1b33; font-weight: 700; }
        td, th { border: 1px solid #94a3b8; padding: 9px 10px; min-width: 80px; vertical-align: top; }
        tbody tr:nth-child(even) { background: #f8fafc; }
      `,
      skin_url: @json(asset('vendor/tinymce/skins/ui/oxide')),
      content_css: false,
      setup: (editor) => {
        editor.ui.registry.addButton('choosephoto', {
          text: 'Choose photo',
          tooltip: 'Upload a photo from your computer',
          icon: 'image',
          onAction: async () => {
            try {
              const file = await pickImageFile();
              const url = await uploadImageFile(file);
              insertPhoto(editor, url);
            } catch (err) {
              if (err !== 'cancelled') editor.notificationManager.open({ text: String(err), type: 'error' });
            }
          }
        });
      },
      file_picker_callback: (callback, value, meta) => {
        if (meta.filetype !== 'image') return;
        pickImageFile()
          .then((file) => uploadImageFile(file))
          .then((url) => callback(url, { alt: '' }))
          .catch((err) => {
            if (err !== 'cancelled') alert(String(err));
          });
      },
      images_upload_handler: (blobInfo) => uploadImageFile(blobInfo.blob())
    }).then(() => {
      bindChoosePhotoButtons();
    });

    document.querySelectorAll('form').forEach((form) => {
      form.addEventListener('submit', () => {
        if (window.tinymce) tinymce.triggerSave();
      });
    });

    document.addEventListener('toggle', (e) => {
      if (e.target && e.target.classList && e.target.classList.contains('tab-block')) {
        bindChoosePhotoButtons();
      }
    }, true);
  })();
</script>
