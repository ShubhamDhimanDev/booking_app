<style>
body.yp-body { padding-bottom: 72px; }
body.yp-body .st-wrap { padding-top: 12px; }
.yp {
    --ink: #373c80; --gold: #C9973A; --gold-pale: #F9F0DC; --gold-faint: #FDF8F0; --cream: #FAF6EF; --parchment: #F5EDD8;
    --white: #fff; --muted: #7A6F5E; --soft: #3D3422; --border: rgba(201,151,58,.18);
    --red: #8B1A1A; --red-pale: #F8EEEE; --green: #1A5C2C; --green-pale: #EEF8F1;
    font-family: 'DM Sans', sans-serif; color: var(--ink); line-height: 1.7;
}
.yp h1, .yp h2, .yp h3 { font-family: 'Cormorant Garamond', serif; font-weight: 600; line-height: 1.25; }
.yp a { color: inherit; }
.yp svg { flex-shrink: 0; }

/* Breadcrumb */
.yp-bc { display: flex; gap: 6px; align-items: center; flex-wrap: wrap; font-size: 12px; color: var(--muted); padding: 8px 0 0; }
.yp .yp-bc a { color: var(--muted); text-decoration: none; }
.yp .yp-bc a:hover { color: var(--ink); }
.yp-bc b { font-weight: 500; color: var(--ink); }

/* Hero */
.yp-hero { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 64px; align-items: start; padding: 24px 0 60px; }
.yp-frame { background: var(--ink); border-radius: 4px; aspect-ratio: 1; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; }
.yp-frame::before { content: ''; position: absolute; inset: 0; background: radial-gradient(circle at 50% 50%, rgba(201,151,58,.08) 0%, transparent 70%); pointer-events: none; }
.yp-frame img { width: 100%; height: 100%; object-fit: cover; display: block; }
.yp-frame .yp-yantra-wrap { width: 70%; height: 70%; }
.yp-yantra { width: 100%; height: 100%; animation: yp-spin 3600s linear infinite; }
@keyframes yp-spin { to { transform: rotate(360deg); } }
@media (prefers-reduced-motion: reduce) { .yp-yantra { animation: none; } }
.yp-stock { position: absolute; top: 14px; left: 14px; z-index: 1; font-size: 10px; font-weight: 500; padding: 4px 10px; border-radius: 2px; letter-spacing: .04em; text-transform: uppercase; background: var(--green-pale); color: var(--green); }
.yp-stock.out { background: var(--red-pale); color: var(--red); }
.yp-thumbs { display: flex; gap: 10px; margin-top: 12px; flex-wrap: wrap; }
.yp-thumb { width: 64px; height: 64px; background: var(--ink); border-radius: 3px; cursor: pointer; border: 1.5px solid transparent; padding: 0; overflow: hidden; opacity: .7; transition: border-color .2s, opacity .2s; }
.yp-thumb.active { border-color: var(--gold); opacity: 1; }
.yp-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }

.yp-info { display: flex; flex-direction: column; gap: 18px; }
.yp-cat { display: inline-flex; align-items: center; gap: 6px; background: var(--gold-pale); color: var(--gold); font-size: 11px; font-weight: 500; padding: 4px 12px; border-radius: 2px; letter-spacing: .05em; text-transform: uppercase; width: fit-content; }
.yp h1.yp-title { font-size: 40px; color: var(--ink); line-height: 1.15; font-weight: 500; margin: -4px 0 0; }
.yp-sub { font-size: 13px; color: var(--muted); font-style: italic; }
.yp-benefits { display: flex; flex-direction: column; gap: 7px; margin: 0; padding: 0; list-style: none; }
.yp-benefits li { display: flex; align-items: flex-start; gap: 9px; font-size: 13px; font-weight: 500; }
.yp-dot { width: 18px; height: 18px; border-radius: 50%; background: var(--green-pale); color: var(--green); display: flex; align-items: center; justify-content: center; margin-top: 1px; }
.yp-purpose { font-size: 13px; color: var(--soft); line-height: 1.75; padding: 12px 16px; background: var(--gold-faint); border-left: 3px solid var(--gold); border-radius: 0 4px 4px 0; }
.yp-rating { display: flex; align-items: center; gap: 10px; }
.yp-stars { color: var(--gold); font-size: 14px; letter-spacing: 2px; }
.yp-rcount { font-size: 12px; color: var(--muted); }
.yp-pills { display: flex; gap: 10px; flex-wrap: wrap; }
.yp-pill { padding: 6px 16px; border-radius: 20px; font-size: 12px; font-weight: 500; background: var(--gold); color: #fff; }
.yp-price-row { display: flex; align-items: baseline; gap: 12px; flex-wrap: wrap; }
.yp-price { font-family: 'Cormorant Garamond', serif; font-size: 38px; font-weight: 500; color: var(--ink); }
.yp-mrp { font-size: 16px; color: var(--muted); text-decoration: line-through; }
.yp-off { font-size: 11px; background: var(--green-pale); color: var(--green); padding: 3px 8px; border-radius: 2px; font-weight: 500; }
.yp-trust { background: var(--gold-faint); border: 1px solid var(--border); border-radius: 4px; padding: 14px 16px; display: flex; flex-direction: column; gap: 7px; }
.yp-trust div { display: flex; align-items: center; gap: 9px; font-size: 12px; color: var(--soft); }
.yp-trust svg { color: var(--gold); }
.yp-var-label { font-size: 12px; font-weight: 500; color: var(--muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: .05em; }
.yp-vpill { display: inline-block; padding: 7px 16px; border: 1px solid var(--gold); color: var(--gold); background: var(--gold-pale); border-radius: 2px; font-size: 13px; }

.yp .yp-btn { display: block; width: 100%; text-align: center; padding: 15px; background: var(--ink); color: var(--parchment); border: 1px solid var(--ink); border-radius: 2px; font: 500 14px 'DM Sans', sans-serif; cursor: pointer; letter-spacing: .04em; text-decoration: none; transition: background .2s, color .2s; }
.yp .yp-btn:hover { background: #2d3070; }
.yp .yp-btn[disabled] { background: #9a9ab5; border-color: #9a9ab5; cursor: not-allowed; }
.yp .yp-btn.ghost { background: transparent; color: var(--ink); font-size: 13px; padding: 14px; letter-spacing: .03em; }
.yp .yp-btn.ghost:hover { background: var(--ink); color: var(--parchment); }
.yp-low { font-size: 12px; color: var(--red); margin-top: -8px; }
.yp-delivery { display: flex; border: 1px solid var(--border); border-radius: 4px; overflow: hidden; }
.yp-delivery div { display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 11px; color: var(--muted); flex: 1; padding: 9px 8px; border-right: 1px solid var(--border); }
.yp-delivery div:last-child { border-right: 0; }

/* Content column */
.yp-content { max-width: 800px; margin: 0 auto; }
.yp-sec { padding: 3rem 0; border-top: 1px solid var(--border); }
.yp-sec:last-child { padding-bottom: 1rem; }
.yp-eyebrow { font-size: 11px; font-weight: 500; color: var(--gold); letter-spacing: .08em; text-transform: uppercase; margin-bottom: 8px; display: block; }
.yp .yp-h { font-size: 30px; font-weight: 500; color: var(--ink); margin: 0 0 20px; }
.yp-p { font-size: 14px; color: var(--soft); line-height: 1.8; margin: 0 0 1em; }
.yp-p:last-child { margin-bottom: 0; }
.yp-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.yp-card { background: #fff; border: 1px solid var(--border); border-radius: 4px; padding: 16px; display: flex; gap: 12px; align-items: flex-start; }
.yp-card.col { flex-direction: column; gap: 10px; }
.yp-card-h { display: flex; align-items: center; gap: 10px; font-size: 14px; font-weight: 700; color: var(--ink); }
.yp-ico { width: 32px; height: 32px; border-radius: 50%; background: var(--gold-faint); border: 1px solid var(--border); color: var(--gold); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.yp-cp { font-size: 13px; color: var(--soft); line-height: 1.55; margin: 0; }
.yp-cp strong { color: var(--ink); }

.yp-two { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.yp-box { border-radius: 4px; padding: 20px; }
.yp-box.yes { background: var(--green-pale); border: 1px solid rgba(26,92,44,.15); }
.yp-box.no { background: var(--red-pale); border: 1px solid rgba(139,26,26,.12); }
.yp-box-h { font-size: 12px; font-weight: 500; margin-bottom: 14px; letter-spacing: .03em; }
.yp-box.yes .yp-box-h, .yp-box.yes .yp-i svg { color: var(--green); }
.yp-box.no .yp-box-h, .yp-box.no .yp-i svg { color: var(--red); }
.yp-i { display: flex; gap: 8px; font-size: 12px; line-height: 1.55; margin-bottom: 10px; align-items: flex-start; color: var(--soft); }
.yp-i svg { margin-top: 2px; }

.yp-step { display: flex; gap: 20px; padding: 20px 0; border-bottom: 1px solid var(--border); }
.yp-step:last-child { border-bottom: 0; }
.yp-num { width: 36px; height: 36px; border-radius: 50%; background: var(--ink); color: var(--gold); display: flex; align-items: center; justify-content: center; font: 500 17px 'Cormorant Garamond', serif; flex-shrink: 0; margin-top: 2px; }
.yp-step-h { font-size: 15px; font-weight: 500; color: var(--ink); margin-bottom: 5px; }
.yp-step-p { font-size: 13px; color: var(--muted); line-height: 1.65; margin: 0; }

.yp-energy { background: var(--ink); border-radius: 4px; padding: 32px; color: var(--parchment); }
.yp-who { display: flex; align-items: center; gap: 14px; margin-bottom: 20px; }
.yp-avatar { width: 48px; height: 48px; border-radius: 50%; background: rgba(201,151,58,.15); border: 1px solid rgba(201,151,58,.3); display: flex; align-items: center; justify-content: center; font: 500 14px 'Cormorant Garamond', serif; color: var(--gold); flex-shrink: 0; }
.yp-who-n { font-size: 14px; font-weight: 500; }
.yp-who-r { font-size: 11px; color: rgba(245,237,216,.5); }
.yp-quote { font-family: 'Cormorant Garamond', serif; font-size: 18px; font-style: italic; line-height: 1.7; margin: 0 0 24px; padding-bottom: 24px; border-bottom: 1px solid rgba(201,151,58,.2); }
.yp-checks { display: flex; flex-direction: column; gap: 10px; }
.yp-checks div { display: flex; align-items: flex-start; gap: 10px; font-size: 13px; color: rgba(245,237,216,.75); line-height: 1.5; }
.yp-checks svg { color: var(--gold); margin-top: 3px; }

.yp-use-h { font-size: 13px; font-weight: 700; color: var(--ink); margin-bottom: 5px; }
.yp-use-p { font-size: 12px; color: var(--muted); line-height: 1.6; margin: 0; }
.yp-card.use { display: block; padding: 18px; }

.yp-table { width: 100%; border-collapse: collapse; border: 1px solid var(--border); }
.yp-table th { font-size: 11px; font-weight: 500; padding: 12px 16px; text-align: left; background: var(--ink); color: var(--gold); letter-spacing: .05em; text-transform: uppercase; }
.yp-table td { font-size: 13px; padding: 12px 16px; vertical-align: top; line-height: 1.5; border-bottom: 1px solid var(--border); }
.yp-table tr:last-child td { border-bottom: 0; }
.yp-table tbody tr:nth-child(even) { background: var(--gold-faint); }
.yp-table td:first-child { font-weight: 500; color: var(--ink); font-size: 12px; text-transform: uppercase; letter-spacing: .03em; width: 30%; }
.yp-table .good { color: var(--green); font-weight: 500; }
.yp-table .bad { color: var(--red); }
.yp-table td span { display: inline-flex; align-items: flex-start; gap: 5px; }
.yp-table td svg { margin-top: 3px; }

.yp-note { display: flex; gap: 20px; padding: 28px; background: var(--gold-faint); border: 1px solid var(--border); border-radius: 4px; }
.yp-note .yp-avatar { width: 52px; height: 52px; background: var(--ink); border: 2px solid var(--gold); }
.yp-note-q { font: italic 17px/1.75 'Cormorant Garamond', serif; color: var(--soft); margin: 10px 0 0; }
.yp-note .yp-who-n { color: var(--ink); }
.yp-note .yp-who-r { color: var(--muted); }

.yp-specs { background: #fff; border: 1px solid var(--border); border-radius: 4px; overflow: hidden; }
.yp-spec { display: flex; border-bottom: 1px solid var(--border); padding: 12px 20px; gap: 12px; }
.yp-spec:last-child { border-bottom: 0; }
.yp-spec b { font-size: 12px; font-weight: 500; color: var(--muted); text-transform: uppercase; letter-spacing: .04em; width: 40%; flex-shrink: 0; }
.yp-spec span { font-size: 13px; color: var(--ink); }

.yp-rbar { display: flex; gap: 24px; background: #fff; border: 1px solid var(--border); border-radius: 4px; padding: 24px; margin-bottom: 16px; align-items: center; }
.yp-big { text-align: center; }
.yp-big-n { font: 500 52px/1 'Cormorant Garamond', serif; color: var(--ink); }
.yp-big .yp-stars { font-size: 15px; display: block; margin: 4px 0; }
.yp-bars { flex: 1; display: flex; flex-direction: column; gap: 5px; }
.yp-bar { display: flex; align-items: center; gap: 8px; font-size: 11px; color: var(--muted); }
.yp-bar b { width: 24px; text-align: right; font-weight: 400; }
.yp-bar i { flex: 1; height: 5px; background: var(--gold-pale); border-radius: 3px; overflow: hidden; }
.yp-bar u { display: block; height: 100%; background: var(--gold); border-radius: 3px; }
.yp-bar span { width: 24px; }
.yp-rv { background: #fff; border: 1px solid var(--border); border-radius: 4px; padding: 20px; margin-bottom: 12px; }
.yp-rv-top { display: flex; justify-content: space-between; gap: 12px; margin-bottom: 10px; }
.yp-rv-n { font-size: 13px; font-weight: 500; color: var(--ink); }
.yp-rv-l { font-size: 11px; color: var(--muted); }
.yp-rv-q { font-size: 13px; color: var(--soft); line-height: 1.7; font-style: italic; margin: 0 0 10px; }
.yp-rv-tag { display: inline-block; font-size: 10px; background: var(--green-pale); color: var(--green); padding: 3px 8px; border-radius: 2px; font-weight: 500; }

.yp-faq details { border-bottom: 1px solid var(--border); padding: 16px 0; }
.yp-faq summary { cursor: pointer; list-style: none; display: flex; justify-content: space-between; align-items: center; gap: 12px; font-size: 14px; font-weight: 500; color: var(--ink); }
.yp-faq summary::-webkit-details-marker { display: none; }
.yp-faq summary::after { content: '+'; font-size: 20px; color: var(--muted); transition: transform .2s; }
.yp-faq details[open] summary::after { transform: rotate(45deg); }
.yp-faq p { font-size: 13px; color: var(--muted); line-height: 1.75; margin: 10px 0 0; padding-right: 28px; }

.yp-rel { display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; }
.yp a.yp-rel-card { display: block; background: #fff; border: 1px solid var(--border); border-radius: 4px; padding: 16px; text-decoration: none; transition: border-color .2s; }
.yp a.yp-rel-card:hover { border-color: var(--gold); }
.yp-rel-img { background: var(--ink); border-radius: 3px; height: 120px; display: flex; align-items: center; justify-content: center; margin-bottom: 12px; overflow: hidden; }
.yp-rel-img img { width: 100%; height: 100%; object-fit: cover; }
.yp-rel-img svg { width: 56px; height: 56px; }
.yp-rel-n { font-size: 13px; font-weight: 700; color: var(--ink); margin-bottom: 3px; line-height: 1.4; }
.yp-rel-u { font-size: 11px; color: var(--muted); margin-bottom: 9px; }
.yp-rel-p { font: 500 18px 'Cormorant Garamond', serif; color: var(--ink); }

/* Fixed bottom bar */
.yp-sticky { position: fixed; bottom: 0; left: 0; right: 0; background: rgba(250,246,239,.96); backdrop-filter: blur(14px); border-top: 1px solid var(--border); padding: 12px 20px; z-index: 50; }
.yp-sticky-in { max-width: 1120px; margin: 0 auto; padding: 0 20px; display: flex; align-items: center; justify-content: space-between; gap: 16px; }
.yp-sticky-l { display: flex; align-items: center; gap: 16px; min-width: 0; }
.yp-sticky-n { font-size: 13px; font-weight: 500; color: var(--ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.yp-sticky-s { font-size: 11px; color: var(--muted); }
.yp-sticky-p { font: 500 24px 'Cormorant Garamond', serif; color: var(--ink); white-space: nowrap; }
.yp-sticky-r { display: flex; gap: 10px; align-items: center; }
.yp .yp-sticky .yp-btn { width: auto; padding: 11px 24px; font-size: 13px; white-space: nowrap; }

@media (max-width: 800px) {
    .yp-hero { grid-template-columns: 1fr; gap: 32px; padding-bottom: 40px; }
    .yp h1.yp-title { font-size: 32px; }
    .yp-grid, .yp-two, .yp-rel { grid-template-columns: 1fr; }
    .yp-sticky-in { padding: 0; }
    .yp-sticky-l > div:first-child { display: none; }
    .yp .yp-sticky .yp-btn { padding: 11px 14px; }
    .yp-rbar { flex-direction: column; align-items: stretch; }
    .yp-note { flex-direction: column; }
    .yp-spec { flex-direction: column; gap: 2px; }
    .yp-spec b { width: auto; }
    .yp-table td:first-child { width: auto; }
    .yp-table th, .yp-table td { padding: 10px; }
}
@media (max-width: 420px) {
    .yp .yp-sticky .yp-btn.ghost { display: none; }
}
</style>
