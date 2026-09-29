@once
<style>
    .cms-container { max-width: 1120px; margin: 0 auto; padding: 0 20px; }
    .cms-header { background: #fff; border-bottom: 1px solid #e2e8f0; position: sticky; top: 0; z-index: 40; }
    .cms-header .cms-container { display: flex; align-items: center; justify-content: space-between; gap: 16px; min-height: 64px; flex-wrap: wrap; }
    .cms-brand { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 1.15rem; color: #0f172a; text-decoration: none; }
    .cms-brand img { height: 40px; width: auto; }
    .cms-nav { display: flex; align-items: center; gap: 22px; flex-wrap: wrap; }
    .cms-nav a { color: #475569; text-decoration: none; font-weight: 500; font-size: .95rem; }
    .cms-nav a:hover { color: #4f46e5; }
    .cms-footer { background: #0f172a; color: #cbd5e1; margin-top: 64px; padding: 36px 0; font-size: .9rem; }
    .cms-footer a { color: #cbd5e1; text-decoration: none; }
    .cms-footer a:hover { color: #fff; }
    .cms-footer .cms-nav { margin-bottom: 14px; }
    .cms-footer .cms-nav a { color: #cbd5e1; }
    .cms-tz { display: flex; justify-content: flex-end; align-items: center; gap: 8px; font-size: .8rem; color: #64748b; padding: 8px 0; }
    .cms-tz select { max-width: 220px; font-size: .8rem; padding: 3px 6px; border: 1px solid #cbd5e1; border-radius: 6px; background: transparent; color: inherit; }

    .cms-section { padding: 56px 0; }
    .cms-section h2 { font-size: 1.9rem; margin: 0 0 20px; color: #0f172a; }
    .cms-hero { background: #1e1b4b center/cover no-repeat; color: #fff; text-align: center; padding: 96px 0; }
    .cms-hero h1 { font-size: 2.6rem; margin: 0 0 14px; }
    .cms-hero p { font-size: 1.15rem; max-width: 640px; margin: 0 auto 26px; opacity: .92; }
    .cms-btn { display: inline-block; background: #4f46e5; color: #fff; text-decoration: none; font-weight: 600; padding: 12px 26px; border-radius: 10px; }
    .cms-btn:hover { background: #4338ca; }
    .cms-text { line-height: 1.7; color: #334155; }
    .cms-image img { max-width: 100%; height: auto; border-radius: 12px; display: block; margin: 0 auto; }
    .cms-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 20px; }
    .cms-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 22px; }
    .cms-card h3 { margin: 0 0 8px; font-size: 1.1rem; }
    .cms-price { font-weight: 700; font-size: 1.25rem; color: #4f46e5; margin: 10px 0 14px; }
    .cms-faq details { border-bottom: 1px solid #e2e8f0; padding: 14px 0; }
    .cms-faq summary { cursor: pointer; font-weight: 600; }
    .cms-faq p { margin: 10px 0 0; color: #475569; line-height: 1.6; }
    .cms-quote { font-style: italic; color: #334155; margin: 0 0 10px; }
    .cms-cta { background: #eef2ff; text-align: center; }
    @media (prefers-color-scheme: dark) {
        .cms-tz { color: #94a3b8; }
    }
</style>
@endonce
