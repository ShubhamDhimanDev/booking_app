<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

/**
 * Creates the Career Growth Yantra and Vivah Yantra store products with their page content.
 * Safe to re-run: products are matched by slug and their content is refreshed.
 * Prices are only created for INR countries; add other countries in Admin > Products.
 * Touches only the store tables (never bookings or events).
 *
 *   php artisan db:seed --class=YantraProductsSeeder
 */
class YantraProductsSeeder extends Seeder
{
    public function run()
    {
        $category = ProductCategory::firstOrCreate(['slug' => 'yantras'], ['name' => 'Yantras', 'is_active' => true]);

        foreach ([$this->career(), $this->vivah()] as $def) {
            $product = Product::updateOrCreate(['slug' => $def['slug']], [
                'category_id' => $category->id,
                'name' => $def['name'],
                'short_description' => $def['short'],
                'description' => null,
                'content' => Product::sanitizeContent($def['content'] + $this->shared($def)),
                'track_stock' => false,
                'is_active' => true,
                'grants_free_session' => true, // only takes effect once a free-session event is set in Admin
            ]);

            foreach (Country::where('currency', 'INR')->get() as $country) {
                $product->prices()->firstOrCreate(['country_id' => $country->id], [
                    'currency' => 'INR', 'mrp' => 15999, 'sale_price' => 11499,
                ]);
            }
        }
    }

    /** Copy that is identical on both yantras. */
    protected function shared(array $def): array
    {
        return [
            'subtitle' => 'Personally energised by Chaitanya for you based on your energy',
            'energy_checks' => [
                'Each yantra is activated individually — never in batches',
                $def['kriya'] . ' performed by Chaitanya personally before dispatch',
                'Energisation completed before the yantra leaves — never after arrival',
                'One private consultation with Chaitanya included in this purchase',
            ],
            'usage' => $def['placement'] + [
                2 => ['title' => 'Cleaning', 'body' => 'Wipe with a dry or slightly damp cloth only. Do not use chemical cleaners or submerge in water. For copper — a light polish once a month keeps the energy active.'],
                3 => ['title' => 'Storage', 'body' => 'If storing temporarily, wrap in a clean red or white cloth. Do not place on the floor. Keep away from direct sunlight for extended periods.'],
            ],
            'specs' => [
                ['key' => 'Material', 'value' => 'Copper'],
                ['key' => 'Size', 'value' => '17 × 17 cm'],
                ['key' => 'Finish', 'value' => 'Hand-etched sacred geometric pattern'],
                ['key' => 'Water resistance', 'value' => 'Not waterproof — wipe clean only'],
                ['key' => 'Tarnish resistance', 'value' => 'Light tarnishing over time (natural for copper)'],
                ['key' => 'Dispatch time', 'value' => '5–7 working days after energisation'],
                ['key' => 'Consultation', 'value' => $def['consult_spec']],
            ],
            'not_cards' => [
                ['title' => $def['not_first'][0], 'body' => $def['not_first'][1]],
                ['title' => $def['not_second'][0], 'body' => $def['not_second'][1]],
                ['title' => 'Not a religious object.', 'body' => 'It is not tied to any deity, religion, or belief system. It is an energy instrument. People of all backgrounds use it.'],
                ['title' => 'Not an overnight fix.', 'body' => $def['not_overnight']],
            ],
            'faq' => array_merge([
                ['q' => 'Do I need my birth date or horoscope?', 'a' => 'No. Astro Chaitanya products are energy-based — not chart-based. No birth date, no kundli, no prior information is required to purchase this yantra. The included consultation also requires no birth chart — Chaitanya reads your present energy directly.'],
            ], $def['faq'], [
                ['q' => 'Can I use this alongside other spiritual items?', 'a' => 'Yes. This yantra does not conflict with other energy or spiritual practices. It functions independently.'],
                ['q' => 'Is this religious?', 'a' => 'No. This product is not tied to any religion, deity, or belief system. It is an energy instrument — it works through the energetic properties of the geometric pattern and the Kriya, not through religious practice or belief. People of all backgrounds and beliefs use it.'],
            ]),
        ];
    }

    protected function career(): array
    {
        return [
            'slug' => 'career-growth-yantra',
            'name' => 'Career Growth Yantra',
            'short' => 'A copper yantra personally energised to remove the energetic blocks holding your career back.',
            'kriya' => 'Career Growth Kriya',
            'consult_spec' => 'One 30-minute private session with Chaitanya · reads your career energy field · included in purchase · scheduled within 7 days of dispatch',
            'placement' => [
                ['title' => 'Placement — home', 'body' => 'Place the yantra where you work at home — on your desk or the wall directly in front of you. The face of the yantra should be visible — not tucked away. Keep the area clean and uncluttered.'],
                ['title' => 'Placement — business', 'body' => 'Place at or near the entrance of your business space, facing inward. For offices, place on the wall facing the main door or at your primary workspace.'],
            ],
            'not_first' => ['Not a lucky charm.', 'It does not create luck or attract random fortune. It removes specific energetic blocks that have been preventing a natural outcome.'],
            'not_second' => ['Not a replacement for effort.', 'The yantra works on what is energetically blocking the outcome your effort deserves. It requires the effort to already be there.'],
            'not_overnight' => 'Career energy shifts gradually. The block has been operating for some time. The correction works at the same root level — which takes time to reflect in external circumstances.',
            'faq' => [
                ['q' => 'Is this customised for me personally?', 'a' => 'Each yantra is energised individually by Chaitanya, and the included consultation reads your own career energy field first — so the Kriya is aligned to the root found in your session, not a generic blessing.'],
                ['q' => 'How long until I notice a change?', 'a' => 'Career shifts depend on the specific situation. Some people notice conversations and opportunities opening within 4–6 weeks. Others see the primary shift over 3–4 months. The yantra works at the root level — the visible career changes follow as the energetic block clears. Results are not guaranteed.'],
                ['q' => 'What does the included consultation cover?', 'a' => 'The included 30-minute session with Chaitanya covers a direct reading of your current career energy field. He identifies the specific root causing your pattern — whether growth suppression, external projection from the workplace, or an ancestral pattern — and confirms what the yantra is designed to address in your situation. It is scheduled within 7 days of your yantra being dispatched.'],
            ],
            'content' => [
                'benefits' => [
                    'Removes energetic blocks holding your career back',
                    'Supports recognition, growth and financial progress',
                    'Works without birth chart, ritual or superstition',
                ],
                'purpose' => 'For professionals and business owners experiencing career stagnation, promotion blocks, salary ceilings, or the persistent feeling that effort and outcome are not aligned — despite doing everything right.',
                'highlights' => ['Removes career blocks', 'Clears promotion ceiling', 'Restores financial flow', 'Includes consultation'],
                'benefits_intro' => 'The Career Growth Yantra addresses four specific career patterns — each one a distinct situation where effort and outcome are misaligned. It is designed for the person who has done everything right and is still stuck.',
                'benefit_cards' => [
                    ['title' => 'Job change', 'body' => 'Removes the energetic block that has been preventing career transition — despite capability, applications, and interviews going well. When everything is right and the move still does not happen, the block is energetic.'],
                    ['title' => 'Promotion', 'body' => 'Clears the growth suppression pattern creating a ceiling on recognition. When capability is acknowledged but the promotion keeps going to someone else — cycle after cycle — the root is in the professional energy field.'],
                    ['title' => 'Work environment', 'body' => 'Restores personal energy field integrity so hostile, draining, or artificially pressured environments stop depleting you. When the environment follows you from one company to the next — the root is in your field, not the workplace.'],
                    ['title' => 'Salary increase', 'body' => 'Addresses the financial flow block keeping compensation below market and below track record. When peers with less experience earn more, and single-digit increments repeat year after year — there is an energetic ceiling on financial recognition.'],
                ],
                'who_yes' => [
                    'You have been doing everything right — and the career still does not reflect what you are capable of',
                    'The pattern of stagnation or non-recognition has persisted across more than one role or company',
                    'You want to understand what is creating the block — not just hope it resolves on its own',
                    'You are willing to commit to the process — placing the yantra correctly and attending the consultation',
                ],
                'who_no' => [
                    'You are looking for a shortcut that replaces skill, effort, or professional development',
                    'You expect an immediate or dramatic career change within days of purchase',
                    'You have not yet put in the work — the yantra removes blocks, not the need for capability',
                    'You want a prediction about your career future — this is energetic correction, not fortune telling',
                ],
                'about_heading' => 'What is the Career Growth Yantra?',
                'about' => "The Career Growth Yantra is a sacred geometric instrument designed to address energetic blocks in the career and professional field — the invisible patterns that prevent capable, hardworking people from receiving the recognition, growth, and financial reward their effort deserves.\n\nVritti means profession or livelihood. Vijaya means success and victory. Together, this yantra is specifically designed to remove what is holding the career back at the energetic root — whether that is growth suppression, external projection from the workplace, or an ancestral pattern around professional recognition and financial flow.\n\nEach piece is personally activated by Chaitanya before dispatch. The energisation is specific to career and professional energy — not generic.",
                'signs' => [
                    'You have been trying to change jobs for months — interviews go well, nothing converts',
                    'Promotion keeps going to someone else despite acknowledged capability and strong performance',
                    'Salary has not kept pace — single digit increments year after year despite strong track record',
                    'Peers with fewer years of experience are earning more and progressing faster',
                    'Work environment feels hostile, draining, or artificially pressured with no way out',
                    'You have done everything right — and the career still does not reflect what you are capable of',
                ],
                'steps_heading' => 'How the Career Growth Yantra works',
                'steps' => [
                    ['title' => 'Chaitanya reads your energy first', 'body' => 'Your purchase includes a private 30-minute consultation with Chaitanya. Before the yantra is energised, he reads your current career energy field directly — identifying the specific root causing your pattern. Whether it is growth suppression, external projection from the workplace, or an ancestral pattern around professional recognition and financial flow — the reading confirms what is operating, and what the Kriya needs to address.'],
                    ['title' => 'Chaitanya performs the Career Growth Kriya', 'body' => 'Based on what was found in your reading, Chaitanya personally energises your yantra using the Career Growth Kriya — aligned precisely to the root identified in your session. This is not a generic blessing. The energisation is specific to what your career energy field requires.'],
                    ['title' => 'The yantra addresses your career energy field', 'body' => 'Once placed at your workspace, the Career Growth Yantra works continuously on the energetic patterns that have been creating the ceiling in your career. It does not create shortcuts — it removes what has been blocking a natural outcome that your capability and effort should already have produced.'],
                    ['title' => 'Your professional energy field clears', 'body' => 'Over time, the pattern of stagnation, non-recognition, and financial ceiling begins to reduce. Movement appears in situations that had been stuck — conversations open, opportunities return, recognition begins to come. The yantra removes the energetic interference that has been preventing the right circumstances from reaching you.'],
                ],
                'energy_quote' => 'Every Career Growth Yantra I dispatch goes through a personal Kriya specifically aligned to career and professional energy. I do not energise yantras in bulk. Each piece is activated individually — with a clear intention of removing what has been blocking the person\'s professional growth and recognition.',
                'expect_yes' => [
                    'Movement in career situations that had been stagnant — conversations opening, opportunities returning',
                    'Recognition beginning to come — from managers, peers, or the market — in a way that feels different from before',
                    'Financial conversations beginning to move in your favour after periods of stagnation',
                    'Greater clarity and energy in your professional decisions and interactions',
                ],
                'expect_no' => [
                    'Overnight career transformation — this is gradual, root-level work',
                    'A replacement for skill, effort, or professional development — the yantra removes energetic blocks, not the need for capability',
                    'A universal career solution — this yantra addresses energetic patterns specifically, not external market conditions',
                    'Any ritual, practice, or ongoing commitment required from your side',
                ],
                'note_quote' => 'The Career Growth Yantra is not a lucky charm or a shortcut. It addresses the energetic root of career stagnation — the invisible pattern that prevents capable, hardworking people from receiving what their effort deserves. I have seen this pattern hundreds of times. A person does everything right and the career still does not move. The root is energetic — and this yantra is designed to correct it.',
                'not_intro' => 'The Career Growth Yantra is an energetic instrument — not a lucky charm, not a religious object, and not a substitute for professional effort. Understanding what it is not helps you use it correctly.',
            ],
        ];
    }

    protected function vivah(): array
    {
        return [
            'slug' => 'vivah-yantra',
            'name' => 'Vivah Yantra',
            'short' => 'A copper yantra personally energised to clear the energetic block behind marriage delay and last-stage collapse.',
            'kriya' => 'Vivaha Siddhi Kriya',
            'consult_spec' => 'One 30-minute private session with Chaitanya · reads the marriage energy field · included in purchase · scheduled within 7 days of dispatch',
            'placement' => [
                ['title' => 'Placement — personal space', 'body' => 'Place in your personal space — your bedroom or the room you spend most time in. On the north or east wall, at eye level. The face of the yantra should be visible — not tucked away or covered.'],
                ['title' => 'Placement — home', 'body' => 'If a personal space is not available, place in the main living area of the home. The yantra should be in an area you occupy regularly. Keep the area clean and uncluttered.'],
            ],
            'not_first' => ['Not a matchmaking tool.', 'It does not find a partner or create an alliance. It removes the energetic block that has been preventing the right alliance from completing — the practical process continues alongside it.'],
            'not_second' => ['Not a guarantee of timing.', 'The yantra clears the energetic root of the delay. When and how marriage happens depends on many factors — the yantra addresses the energetic layer, not the full picture.'],
            'not_overnight' => 'The delay or block has been operating for some time. The correction works at the same root level — which takes time to reflect in the external circumstances of the marriage situation.',
            'faq' => [
                ['q' => 'Is this customised for me personally?', 'a' => 'Each yantra is energised individually by Chaitanya, and the included consultation reads your own marriage energy field first — so the Kriya is aligned to the root found in your session, not a generic blessing.'],
                ['q' => 'How long until I notice a change?', 'a' => 'Marriage energy shifts gradually. Some people notice stalled alliances beginning to move within a few weeks; for others the shift takes several months. The yantra works at the root level, and the pattern in your marriage situation changes as that root clears. Results are not guaranteed.'],
                ['q' => 'Will this work if I am not currently in an active alliance?', 'a' => 'Yes. The yantra works on your marriage energy field regardless of whether an alliance is currently active. It addresses the root that has been creating the pattern — clearing it so that when the right alliance forms, it can complete.'],
                ['q' => 'What does the consultation cover?', 'a' => 'The included 30-minute session with Chaitanya covers a direct reading of your marriage energy field. He identifies the specific root creating the delay or collapse pattern — whether an ancestral pattern in the family lineage, external projection directed at marriage prospects, or a block in your own field — and confirms what the yantra is designed to address. It is scheduled within 7 days of your yantra being dispatched.'],
            ],
            'content' => [
                'benefits' => [
                    'Removes the energetic block creating delay in marriage',
                    'Clears the last-stage collapse pattern before finalisation',
                    'Works without birth chart, ritual or superstition',
                ],
                'purpose' => 'For individuals and families where marriage has been delayed without clear reason, or where alliances keep breaking at the final stage despite everything being agreed — a pattern that repeats and cannot be explained.',
                'highlights' => ['Removes marriage delay blocks', 'Clears last-stage collapse pattern', 'Addresses external projection', 'Includes consultation'],
                'benefits_intro' => 'The Vivah Yantra addresses four specific marriage patterns — each one a situation where the right outcome keeps not happening despite everything looking right. It is designed for the person who has done everything asked of them and is still waiting.',
                'benefit_cards' => [
                    ['title' => 'Marriage delay', 'body' => 'Removes the energetic block that has been creating persistent delay despite everything looking right. When meetings happen but nothing converts, when interest fades without explanation, when the right outcome keeps not arriving — the root is energetic.'],
                    ['title' => 'Last-stage collapse', 'body' => 'Clears the pattern of alliances confirming and then falling apart at the final stage. When families agree, dates are discussed, and then without clear explanation it collapses — this specific pattern has an energetic root that this yantra is designed to address.'],
                    ['title' => 'Ancestral pattern', 'body' => 'Addresses inherited energetic patterns in the family lineage that have created the same type of marriage difficulty across generations — the same delay, the same collapse pattern that affected parents or siblings before you.'],
                    ['title' => 'External projection', 'body' => 'Identifies and removes concentrated negative energy directed specifically at your marriage prospects — from someone in your social environment whose resentment or envy activates precisely when a positive alliance is forming.'],
                ],
                'who_yes' => [
                    'Marriage has been delayed and you have done everything asked of you — expanded the search, done remedies, consulted astrologers',
                    'The last-stage collapse has happened more than once — across different alliances, different families, different circumstances',
                    'You want to understand what is creating the block — not just wait and hope it resolves on its own',
                    'You are willing to commit to the process — placing the yantra in your personal space and attending the consultation',
                ],
                'who_no' => [
                    'You are looking for a guarantee that marriage will happen by a specific date or with a specific person',
                    'You expect an immediate result — the block has been operating for some time and the correction works at the same root level',
                    'You have not yet taken the practical steps — updated your search, met families, continued the process. The yantra works alongside the effort, not instead of it',
                    'You want a prediction about when marriage will happen — this is energetic correction, not fortune telling',
                ],
                'about_heading' => 'What is the Vivah Yantra?',
                'about' => "The Vivah Yantra is a sacred geometric instrument designed to address the energetic patterns that prevent marriage from happening — persistent delay despite everything looking right, or alliances that confirm and then collapse at the final stage without explanation.\n\nThe Vivah Yantra is also called Vivaha Siddhi Yantra. Vivaha means marriage. Siddhi means fulfilment, completion, and success. Together, it is specifically designed to clear what is blocking marriage at the energetic root — whether that is ancestral patterns in the family lineage, external projection directed at marriage prospects, or an energetic block within the individual's own field.\n\nEach piece is personally activated by Chaitanya before dispatch. The energisation is specific to the marriage energy field — not generic.",
                'signs' => [
                    'Marriage has been delayed and you have done everything asked — expanded the search, done remedies, met families — and still nothing is converting',
                    'An alliance confirmed, families agreed, and then it collapsed at the final stage without clear explanation',
                    'The same pattern of collapse at the final stage has repeated across different alliances',
                    'Meetings happen but interest fades without explanation, and conversations go silent',
                    'The same delay or difficulty affected a parent or sibling before you — it feels inherited',
                    'Things seem to fall apart exactly when a positive alliance begins to form',
                ],
                'steps_heading' => 'How the Vivah Yantra works',
                'steps' => [
                    ['title' => 'Chaitanya reads your marriage energy first', 'body' => 'Your purchase includes a private 30-minute consultation with Chaitanya. Before the yantra is energised, he reads your current marriage energy field directly — identifying the specific root creating the delay or collapse pattern. Whether it is an ancestral pattern in the family lineage, external projection directed at marriage prospects, or an energetic block within your own field — the reading confirms what is operating and what the Kriya needs to address.'],
                    ['title' => 'Chaitanya performs the Vivaha Siddhi Kriya', 'body' => 'Based on what was found in the reading, Chaitanya personally energises your yantra using the Vivaha Siddhi Kriya — aligned precisely to the root identified in your session. The energisation is specific to what your marriage energy field requires — not a generic blessing.'],
                    ['title' => 'The yantra addresses your marriage energy field', 'body' => 'Once placed in your personal space, the Vivah Yantra works continuously on the energetic patterns that have been preventing marriage from happening — the delay pattern, the last-stage collapse pattern, or the ancestral block that has been operating across the family. It removes what has been blocking a natural outcome.'],
                    ['title' => 'The marriage energy field clears', 'body' => 'Over time, the pattern that has been creating the delay or collapse begins to shift. Alliances that previously stalled begin to move. The last-stage collapse pattern reduces. The energetic interference that was preventing marriage from completing is removed — creating conditions for the right outcome to arrive.'],
                ],
                'energy_quote' => 'Every Vivah Yantra I dispatch goes through a personal Kriya specifically aligned to the marriage energy field. I do not energise yantras in bulk. Each piece is activated individually — with a clear intention of removing what has been blocking marriage at the root, whether that is in the family lineage, in external projection, or in the individual\'s own energy field.',
                'expect_yes' => [
                    'Movement in the marriage situation — alliances that had stalled beginning to progress again',
                    'The last-stage collapse pattern reducing — alliances that previously fell apart at finalisation beginning to hold',
                    'A shift in the family energy around the marriage — the heavy or stuck feeling beginning to lift',
                    'Gradual clarity about the right direction — decisions that previously felt impossible beginning to resolve',
                ],
                'expect_no' => [
                    'Overnight results — the delay or block has been operating for some time and the correction works at the same root level',
                    'A guarantee that a specific person or alliance will result in marriage — the yantra clears the energetic block, not the outcome itself',
                    'A replacement for practical steps — updating your profile, meeting families, continuing the process. The effort and the process continue alongside it',
                    'Any religious ritual or ongoing practice required from your side',
                ],
                'note_quote' => 'The Vivah Yantra addresses the energetic root of what keeps marriage from happening — or from completing at the final stage. I have seen this pattern hundreds of times. Someone who has done everything right — right family, right values, right intention — and the marriage still does not happen. Or it confirms and then collapses. The root is in the energy field. And this yantra is designed to clear it.',
                'not_intro' => 'The Vivah Yantra is an energetic instrument — not a matchmaking tool, not a religious object, and not a guarantee that marriage will happen by a specific date. Understanding what it is not helps you use it with the right expectation.',
            ],
        ];
    }
}
