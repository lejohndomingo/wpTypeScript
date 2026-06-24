import '../scss/admin.scss';
import { qs, qsa, on, ready } from '../utils';
import { saveOptions } from '../api/admin';

/** WordPress media attachment object returned by wp.media */
interface WpMediaAttachment {
  url: string;
  id: number;
  [key: string]: unknown;
}

/** WordPress media frame (Backbone-based modal) */
interface WpMediaFrame {
  open(): void;
  on(event: 'select', handler: () => void): void;
  state(): {
    get(prop: 'selection'): {
      first(): { toJSON(): WpMediaAttachment };
    };
  };
}

function getWpMedia(): (options: {
  title: string;
  button: { text: string };
  multiple: boolean;
  library: { type: string };
}) => WpMediaFrame {
  return window.wp?.media;
}

function getWpMediaL10n(): { addMedia: string; select: string } {
  const wp = window.wp;
  return wp?.media?.view?.l10n ?? { addMedia: 'Add Media', select: 'Select' };
}

function initColorPickers(): void {
  const pickers = qsa<HTMLInputElement>('.color-picker');
  if (pickers.length === 0) return;

  pickers.forEach((el) => {
    const preview = document.createElement('span');
    preview.className = 'wptypescript-color-preview';
    preview.style.cssText = `display:inline-block;width:28px;height:28px;border-radius:3px;margin-left:8px;vertical-align:middle;border:1px solid #ccc;background:${el.value || '#fff'}`;
    el.parentNode?.insertBefore(preview, el.nextSibling);

    on(el, 'input', () => {
      preview.style.background = el.value;
    });
  });

  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  const $ = (window as any).jQuery;
  if ($?.fn?.wpColorPicker) {
    $('.color-picker').wpColorPicker();
  }
}

function initMediaUploader(): void {
  const button = qs<HTMLButtonElement>('#wptypescript_background_image_button');
  if (!button) return;

  const urlInput = qs<HTMLInputElement>('#wptypescript_background_image');
  const idInput = qs<HTMLInputElement>('#wptypescript_background_image_id');
  const media = getWpMedia();
  const l10n = getWpMediaL10n();

  let frame: WpMediaFrame | null = null;

  on(button, 'click', (e) => {
    e.preventDefault();

    if (frame) {
      frame.open();
      return;
    }

    frame = media({
      title: l10n.addMedia,
      button: { text: l10n.select },
      multiple: false,
      library: { type: 'image' },
    });

    frame.on('select', () => {
      const attachment = frame!.state().get('selection').first().toJSON();
      if (urlInput) urlInput.value = attachment.url;
      if (idInput) idInput.value = String(attachment.id);
    });

    frame.open();
  });
}

function initTabSwitching(): void {
  const buttons = qsa<HTMLButtonElement>('.tab-button');
  const contents = qsa<HTMLElement>('.tab-content');

  buttons.forEach((btn) => {
    on(btn, 'click', (e) => {
      e.preventDefault();

      buttons.forEach((b) => b.classList.remove('active'));
      btn.classList.add('active');

      contents.forEach((c) => c.classList.remove('active'));
      const tabId = btn.getAttribute('data-tab');
      if (tabId) {
        document.getElementById(tabId)?.classList.add('active');
      }
    });
  });
}

function initGradientSlider(): void {
  const slider = qs<HTMLInputElement>('#wptypescript_gradient_opacity');
  const preview = qs<HTMLElement>('.wptypescript-gradient-preview');
  if (!slider) return;

  const update = () => {
    if (preview) preview.style.opacity = slider.value;
  };

  on(slider, 'input', update);
  on(slider, 'change', update);
}

function initAjaxSave(): void {
  const form = qs<HTMLFormElement>('#wptypescript-options-form');
  if (!form) return;

  on(form, 'submit', async (e) => {
    e.preventDefault();
    const btn = form.querySelector<HTMLButtonElement>('.wptypescript-ajax-save') ?? form.querySelector<HTMLButtonElement>('button[type="submit"]');
    if (btn) btn.disabled = true;

    const data = new FormData(form);
    const options: Record<string, string> = {};
    data.forEach((value, key) => {
      options[key] = String(value);
    });

    try {
      const result = await saveOptions(options);
      if (result.success) {
        const notice = document.createElement('div');
        notice.className = 'notice notice-success is-dismissible';
        notice.innerHTML = `<p>${result.message ?? 'Settings saved.'}</p>`;
        form.prepend(notice);
        setTimeout(() => notice.remove(), 3000);
      }
    } catch (err) {
      const notice = document.createElement('div');
      notice.className = 'notice notice-error is-dismissible';
      notice.innerHTML = `<p>${err instanceof Error ? err.message : 'Save failed.'}</p>`;
      form.prepend(notice);
    } finally {
      if (btn) btn.disabled = false;
    }
  });
}

ready(() => {
  initColorPickers();
  initMediaUploader();
  initTabSwitching();
  initGradientSlider();
  initAjaxSave();
});
