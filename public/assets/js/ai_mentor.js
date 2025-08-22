/**
 * AI Mentor Rule Engine
 *
 * This module generates personalized tips for the user based on their
 * current performance data.
 */

const rules = [
    // --- High Priority: Urgent Actions ---
    {
        priority: 100,
        condition: data => data.overdue_followups > 0,
        tip: data => `You have ${data.overdue_followups} overdue follow-up(s). Start with these to not lose momentum!`
    },
    {
        priority: 90,
        condition: data => data.hot_leads >= 1 && data.overdue_followups === 0,
        tip: data => `You have ${data.hot_leads} hot lead(s). Call at least one now while they are most interested. Use the product-benefit script.`
    },

    // --- Medium Priority: Proactive Guidance ---
    {
        priority: 50,
        condition: data => data.learning_progress['Sales & Networking'] < 30,
        tip: () => "Your 'Sales & Networking' skills are still developing. Complete a module today to boost your confidence on your next call."
    },
    {
        priority: 50,
        condition: data => data.learning_progress['Product Knowledge'] < 50,
        tip: () => "Deepen your product knowledge. Confident experts close more sales. Watch a product video today."
    },
    {
        priority: 45,
        condition: data => data.streak > 3,
        tip: data => `You're on a ${data.streak}-day streak! Keep the fire burning. Complete one 'prospecting' task to extend it.`
    },
    {
        priority: 40,
        condition: data => data.streak === 0 && data.activity.slice(-2)[0] === 1, // Streak broken yesterday
        tip: () => "Momentum is key. Rebuild it today with 3 quick actions: 1 follow-up, 1 new prospect, and 1 learning video."
    },
    {
        priority: 40,
        condition: data => (data.warm_leads || 0) > 5,
        tip: () => "Your pipeline of warm leads is growing. Nurture 2-3 of them today to move them closer to 'hot'."
    },

    // --- Low Priority: General Tips & Encouragement ---
    {
        priority: 20,
        condition: data => data.pending_tasks > 3,
        tip: () => "Feeling overwhelmed? Just focus on the next single step. Completing one small task can create great momentum."
    },
    {
        priority: 10,
        condition: data => data.streak === 0,
        tip: () => "A new day is a fresh start. Complete just one task today to begin building a new streak."
    },
    {
        priority: 5,
        condition: () => true, // Default tip
        tip: () => "Consistency is more important than intensity. Small, daily actions lead to big results."
    },
    {
        priority: 5,
        condition: () => true, // Default tip
        tip: () => "Review your 'Why'. Reconnecting with your motivation can provide a powerful boost for the day."
    }
];

/**
 * Generates an array of tips based on the provided data.
 * @param {object} data - The user's current performance data.
 * @returns {Array<string>} - An array of tip strings.
 */
function generateTips(data) {
    const applicableTips = rules
        .filter(rule => rule.condition(data))
        .sort((a, b) => b.priority - a.priority); // Sort by highest priority

    // Return the text of the top tips
    return applicableTips.map(rule => typeof rule.tip === 'function' ? rule.tip(data) : rule.tip);
}

/**
 * Renders the generated tips into the DOM.
 * @param {Array<string>} tips - An array of tip strings to display.
 */
function displayTips(tips) {
    const container = document.getElementById('ai-mentor-tips');
    if (!container) {
        console.warn('AI Mentor container not found on this page.');
        return;
    }

    // Display the top 2-3 tips
    const tipsToDisplay = tips.slice(0, 3);

    if (tipsToDisplay.length === 0) {
        container.innerHTML = '<p>No tips for now. Keep up the great work!</p>';
        return;
    }

    const title = document.createElement('h3');
    title.className = 'subsection-title';
    title.textContent = 'Your AI Mentor Suggests:';

    const tipElements = tipsToDisplay.map(tipText => {
        const card = document.createElement('div');
        card.className = 'card ai-mentor-card';
        const cardBody = document.createElement('div');
        cardBody.className = 'card__body';
        const tipP = document.createElement('p');
        tipP.textContent = tipText; // Safely set the text
        cardBody.appendChild(tipP);
        card.appendChild(cardBody);
        return card;
    });

    container.replaceChildren(title, ...tipElements);
}


/**
 * Main exported function to generate and display tips.
 * @param {object} data - The user's performance data.
 */
export function generateAndDisplayTips(data) {
    const tips = generateTips(data);
    displayTips(tips);
}
