<?php
// Role-specific AI Coach prompt packs.
// Each pack returns {title, subtitle, greeting, placeholder, inputHint, pairs[]}.

namespace App\Services\Ai;

class AiCoachPacks
{
    public const roleLabel = [
        'employee' => 'Employee',
        'telecaller' => 'Telecaller',
        'backoffice' => 'Backoffice Staff',
        'agent' => 'Channel Partner',
        'associate' => 'Associate (Field Sales)',
        'customer' => 'Customer',
    ];

    /**
     * @param string $role one of employee|telecaller|backoffice|agent|associate|customer
     * @return array{title:string,subtitle:string,greeting:string,placeholder:string,inputHint:string,pairs:array<array{string,string,string}>}
     */
    public static function pack(string $role): array
    {
        $role = strtolower($role);

        if ($role === 'telecaller') {
            return [
                'title' => '📞 Telecaller Coach',
                'subtitle' => 'Telecaller AI coach — punchy Hinglish, no long essays. You call 50-100 leads daily in Gorakhpur.',
                'greeting' => "Namaste! Main aapka Telecaller Coach hoon. Aap kaisa chal raha hai — aaj 50 calls".str_repeat(" kaise hue?", 1)."<br><br>Right-hand side se ek ready prompt tap karein ya apna sawaal type karein. Follow-ups, objections, scripts — sab yahan.",
                'placeholder' => 'Ask your telecaller coach…',
                'inputHint' => 'Aapka input sirf telecaller workflow ke liye use hota hai.',
                'pairs' => [
                    ['📞', 'Handle a price-objection', 'A lead says "too expensive" — give me 3 Hinglish responses I can use on the call.'],
                    ['📝', 'Draft a follow-up SMS', 'Draft a concise follow-up SMS (max 160 characters) for a lead who asked to think about it yesterday.'],
                    ['⏰', 'Best time to follow up', 'Lead at proposal stage hasn\'t responded in 2 days. What timing and channel should I use for follow-up?'],
                    ['🗣️', 'Open the call', 'Lead just picked up — give me a 20-second opener that hooks a Gorakhpur plot enquiry.'],
                    ['💪', 'Coach me like a champion', 'Give me 3 motivations for hitting today\'s call target in a row.'],
                    ['📋', 'Objection: spouse won\'t agree', 'Lead says spouse won\'t approve the plot. Suggest a respectful Hinglish reply + soft next step.'],
                ],
            ];
        }

        if ($role === 'backoffice') {
            return [
                'title' => '🗂️ Backoffice Coach',
                'subtitle' => 'Backoffice productivity coach — accurate, compliant, bullets. Hinglish or English, you choose.',
                'greeting' => "Good morning! Main aapka Backoffice Coach hoon. Aapka aaj ka targets kya hai — log on, emails, records, ya customer calls?<br><br>Right-hand side se ek prompt tap karein ya apna sawaal type karein.",
                'placeholder' => 'Ask your backoffice coach…',
                'inputHint' => 'Backoffice input needs accurate record handling — accuracy above all.',
                'pairs' => [
                    ['📅', 'Plan my day', 'Give me a schedule for today\'s backoffice work — emails, follow-ups, records, 2 urgent tasks, and a 30-min buffer.'],
                    ['📧', 'Draft a formal email', 'Draft a polite email asking a customer for pending booking documents (Aadhaar + PAN + 2 photos).'],
                    ['🔍', 'Verify a lead', 'A lead profile shows flag mismatch (phone vs email region). What 4 checks should I run before marking it verified?'],
                    ['🗂️', 'Inbox zero plan', 'I have 23 unread emails. Give me a 10-minute plan to triage them with priorities P1/P2/P3.'],
                    ['🧾', 'Report template', 'Give me a 5-line format for a daily backoffice report I can paste into the master sheet.'],
                    ['⚖️', 'Policy lookup', 'Customer asks about cancellation within 7 days — what is our standard policy in 3 bullets?'],
                ],
            ];
        }

        if ($role === 'agent') {
            return [
                'title' => '🤝 Channel Partner Coach',
                'subtitle' => 'Channel partner coach — leads, network building, commission, closing. Practical Hinglish.',
                'greeting' => "Namaste! Main aapka Channel Partner Coach hoon. Aap kaisa raha — naye leads, follow-ups, ya pending callbacks?<br><br>Right-hand side se ek prompt tap karein ya apna sawaal type karein.",
                'placeholder' => 'Ask your channel partner coach…',
                'inputHint' => 'Aap channel partner hain — commission ladder, leads, aur network building pe focus karein.',
                'pairs' => [
                    ['🎯', 'My target this month', 'I\'m an APS channel partner with 12 leads this month. Give me a 10-day action plan to convert 3 into bookings.'],
                    ['🗣️', 'Opening call', 'A buyer clicked my WhatsApp button — give me a 20-second opening for a Gorakhpur township plot.'],
                    ['💸', 'Commission explained', 'Explain the APS agent commission system in 5 simple bullets — direct sale slab, override, bonus, and TDS.'],
                    ['📞', 'Follow-up cadence', 'A lead viwed my property but hasn\'t answered calls for 4 days. Draft the follow-up sequence.'],
                    ['🤝', 'Objection: bad review', 'A lead says someone posted a negative review of APS Dream Home. Best professional reply?'],
                    ['📈', 'Double my leads', 'I\'m averaging 4 leads/month. Give me a realistic weekly routine to double that in 60 days — no spammy hacks.'],
                ],
            ];
        }

        if ($role === 'associate') {
            return [
                'title' => '🚀 Associate Sales Coach',
                'subtitle' => 'Field sales coach — Suryoday, Raghunath, Braj Radha, Budh Bihar: scripts, objections, urgency.',
                'greeting' => "Namaste! Main aapka Field Sales Coach hoon. Kaise chal raha hai — naye walk-ins, pending site visits, ya closed deal?<br><br>Right-hand side se ek prompt tap karein ya apna sawaal type karein.",
                'placeholder' => 'Ask your field sales coach…',
                'inputHint' => 'Aap field associate hain — colony pitches, objections, aur close-the-call focus karein.',
                'pairs' => [
                    ['🗣️', 'Opening script', 'A customer just walked into the site office. Give me a 30-second opening pitch for a Suryoday Colony plot.'],
                    ['💸', 'Price objection', 'Customer says "too expensive" — give me 3 Hinglish replies grounded in our actual pricing ladder.'],
                    ['⚖️', 'Competitor jab', 'Customer says competitor is cheaper — best professional comeback mentioning RERA-verified docs?'],
                    ['📞', 'Close the call', 'A lead keeps saying "I\'m thinking about it". Draft a clean close — outline + booking + urgency tag.'],
                    ['💰', 'Commission ladder', 'Explain my commission slabs clearly — direct sale vs override vs leadership royalty.'],
                    ['🏅', 'Pitch premium colony', 'Draft a 45-second pitch for Braj Radha Nagri — premium tier, family-focused angle.'],
                ],
            ];
        }

        if ($role === 'customer') {
            return [
                'title' => '🏠 Customer Assistant',
                'subtitle' => 'Buyer assistant — plots, EMI, RERA, site visits. Friendly Hinglish, no pressure.',
                'greeting' => "Namaste! Mai aapki plots mein help karne aaya hoon — Suryoday, Raghunath, Braj Radha, Budh Bihar Colony mein.<br><br>EMI kya hai, site visit kaise book karein, documents kya chahiye — sab yahan. Right-hand side se ek prompt tap karein ya apna sawaal type karein.",
                'placeholder' => 'Plots, EMI, RERA, site visit…',
                'inputHint' => 'Aap potential plot buyer hain — colony, budget, EMI, RERA, site visit — sab kuch puchh sakte hain.',
                'pairs' => [
                    ['🔍', 'Best plot for ₹15L', 'I have ₹15 lakhs budget. Which colony and plot size should I look at?'],
                    ['💰', 'EMI calculator', 'If I take ₹8 lakh home loan at 9% interest for 15 years, what\'s my EMI?'],
                    ['📅', 'Book a site visit', 'I want to visit Suryoday Colony this weekend — how do I book a site visit?'],
                    ['📄', 'Documents needed', 'What documents does APS need from me to book a plot?'],
                    ['🤝', 'Is APS RERA registered?', 'Tell me 4-5 buyer-protection points about APS and RERA.'],
                    ['📞', 'Call back help', 'Schedule a callback for me tomorrow 11 AM — I have a question about payment schedule.'],
                ],
            ];
        }

        // default employee pack
        return [
            'title' => '🚀 Employee Coach',
            'subtitle' => 'Productivity + career coach — practical, concise, honest Hinglish.',
            'greeting' => "Good morning! Main aapka Employee Coach hoon. Aaj kaisa raha — tasks, emails, ya booking se related?<br><br>Right-hand side se ek prompt tap karein ya apna sawaal type karein.",
            'placeholder' => 'Ask your employee coach…',
            'inputHint' => 'Aap employee hain — productivity, career, ya daily tasks pe focus karein.',
            'pairs' => [
                ['🎯', 'Set today\'s top-3', 'Help me pick my top 3 tasks for today and order them by impact. Ask me what\'s on my plate.'],
                ['📧', 'Draft email to boss', 'Draft a short email to my manager asking for 2 days of leave next week.'],
                ['📚', 'Quick RERA explain', 'Explain in 4 simple bullets what RERA registration means for the home buyer in simple Hindi/English.'],
                ['💪', 'Motivate me', 'I had a disappointing week — 2 site visits canceled, no bookings. Give me honest encouragement + 2 concrete next actions for today.'],
                ['🧭', 'Career step', 'I\'m a field executive looking to grow. What 3 skills to build in the next 30 days?'],
                ['🧾', 'Salary raise next review', 'How should I prepare for my next performance review to make a strong case for a raise?'],
            ],
        ];
    }
}
