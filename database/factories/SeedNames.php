<?php

namespace Database\Factories;

final class SeedNames
{
    /**
     * @var list<string>
     */
    private const PROJECT_TITLES = [
        'Website Redesign',
        'Mobile App Launch',
        'Customer Portal v2',
        'Annual Report 2026',
        'Office Move — Austin',
        'Q2 Pricing Update',
        'Blue Harbor Relaunch',
        'Oak Street Dashboard',
        'Nina\'s Training Program',
        'Red Brick API Migration',
        'Green Valley Marketing Push',
        'Steve\'s Demo Week',
        'Support Playbook Refresh',
        'Inventory Sync Rollout',
        'Partner Onboarding Hub',
        'Security Audit Follow-up',
        'Brand Guidelines Update',
        'Help Center Rewrite',
        'Checkout Flow Optimization',
        'Data Warehouse Cleanup',
        'CRM Integration',
        'Conference Booth Build',
        'Employee Handbook Revision',
        'Fleet Tracker Pilot',
        'Japan Market Entry',
        'Norway Localization',
        'Brazil Sales Kickoff',
        'Harbor View Analytics',
        'Elm Street Store Opening',
        'Maple Lane Newsletter',
        'Sunset Ridge Renovation',
        'Winter Campaign 2026',
        'Summer Intern Program',
        'Silver Fox Retainer',
        'Copper Kettle Rebrand',
        'River Road Documentation',
        'Pinecrest Hiring Sprint',
        'Cedar Point Compliance Review',
        'Golden Gate Partner Portal',
        'Marco\'s Research Sprint',
        'Lena\'s Content Calendar',
        'Internal Tools Refresh',
        'Billing System Upgrade',
        'Slack Bot Cleanup',
        'Incident Response Drill',
        'Vendor Contract Renewals',
        'Warehouse Labeling Project',
        'Community Forum Launch',
        'Referral Program v3',
        'Accessibility Improvements',
        'Performance Benchmarking',
        'Legacy Code Retirement',
        'Cloud Migration Phase 1',
        'Cloud Migration Phase 2',
        'Customer Survey Analysis',
        'Sales Deck Overhaul',
        'Engineering Offsite Planning',
        'Design System 2.0',
        'Purple Finch Experiment',
        'Quick Brown Fox Benchmark',
        'Highland Park Roadmap',
        'Cross-team OKR Alignment',
        'Budget Forecast 2027',
        'Tax Season Prep',
        'Board Meeting Materials',
        'Investor Update Q1',
        'Product Roadmap Review',
        'Beta Feedback Triage',
        'Launch Retrospective',
        'Post-launch Monitoring',
        'Knowledge Base Migration',
        'Email Template Library',
        'Social Media Calendar',
        'Podcast Season Three',
        'Video Tutorial Series',
        'Trade Show Lead Capture',
        'Merch Store Relaunch',
    ];

    /**
     * @var list<string>
     */
    private const TASK_ACTIONS = [
        'Review',
        'Fix',
        'Update',
        'Draft',
        'Ship',
        'Deploy',
        'Test',
        'Schedule',
        'Confirm',
        'Audit',
        'Prepare',
        'Finalize',
        'Document',
        'Refactor',
        'Migrate',
        'Validate',
        'Approve',
        'Publish',
        'Prototype',
        'Wireframe',
        'Estimate',
        'Prioritize',
        'Assign',
        'Close out',
        'Follow up on',
        'Research',
        'Benchmark',
        'Clean up',
        'Archive',
        'Restore',
    ];

    /**
     * @var list<string>
     */
    private const TASK_OBJECTS = [
        'checkout flow',
        'homepage hero',
        'API docs',
        'onboarding emails',
        'login screen',
        'error messages',
        'pricing table',
        'release notes',
        'support macros',
        'status page copy',
        'billing webhook',
        'search filters',
        'mobile navigation',
        'team permissions',
        'export pipeline',
        'backup routine',
        'staging deploy',
        'customer survey',
        'vendor contract',
        'training slides',
        'FAQ entries',
        'dashboard widgets',
        'notification settings',
        'invoice template',
        'roadmap draft',
        'sprint board',
        'meeting agenda',
        'incident report',
        'compliance checklist',
        'analytics tags',
        'email footer',
        'landing page form',
        'partner agreement',
        'inventory report',
        'help article',
        'design mockups',
        'test plan',
        'rollback plan',
        'launch checklist',
        'postmortem notes',
    ];

    /**
     * @var list<string>
     */
    private const COLORS = [
        'Blue',
        'Red',
        'Green',
        'Gold',
        'Silver',
        'Purple',
        'Coral',
        'Amber',
        'Teal',
        'Crimson',
        'Indigo',
        'Ivory',
    ];

    /**
     * @var list<string>
     */
    private const ANIMALS = [
        'Monkey',
        'Falcon',
        'Otter',
        'Badger',
        'Heron',
        'Panda',
        'Lynx',
        'Fox',
        'Crane',
        'Walrus',
    ];

    /**
     * @var list<string>
     */
    private const FIRST_NAMES = [
        'Steve',
        'Nina',
        'Marco',
        'Lena',
        'Omar',
        'Priya',
        'Theo',
        'Grace',
        'Felix',
        'Ava',
        'Jonah',
        'Maya',
    ];

    /**
     * @var list<string>
     */
    private const STREETS = [
        'Oak Street',
        'Elm Lane',
        'Harbor View',
        'Maple Road',
        'River Drive',
        'Cedar Point',
        'Pinecrest',
        'Sunset Ridge',
        'Highland Park',
        'Golden Gate',
    ];

    /**
     * @var list<string>
     */
    private const COUNTRIES = [
        'Norway',
        'Japan',
        'Brazil',
        'Canada',
        'Ireland',
        'Portugal',
        'Kenya',
        'India',
        'Mexico',
        'Sweden',
    ];

    /**
     * @var list<string>
     */
    private const PROJECT_SUFFIXES = [
        'Phase 2',
        '2026',
        'Q3',
        'Q4',
        'Pilot',
        'Refresh',
        'v2',
        'v3',
    ];

    /**
     * @var list<string>
     */
    private const PROJECT_TYPES = [
        'Initiative',
        'Rollout',
        'Program',
        'Sprint',
        'Overhaul',
        'Launch',
        'Migration',
        'Upgrade',
    ];

    /**
     * @var list<string>
     */
    private const DESCRIPTIONS = [
        'Keep this moving before the next review.',
        'Blocked until design signs off.',
        'Low risk, quick win.',
        'Needs a second pair of eyes.',
        'Customer-facing — double-check copy.',
        'Part of the current sprint goal.',
        'Waiting on vendor response.',
        'Good candidate to delegate.',
        'Track in the weekly standup.',
        'Finish before the launch window closes.',
    ];

    public static function projectTitle(): string
    {
        return self::buildProjectTitle();
    }

    public static function taskTitle(): string
    {
        return self::buildTaskTitle();
    }

    public static function checklistTitle(): string
    {
        return self::buildChecklistTitle();
    }

    public static function optionalDescription(): ?string
    {
        return fake()->optional(0.35)->randomElement(self::DESCRIPTIONS);
    }

    private static function buildProjectTitle(): string
    {
        $roll = fake()->numberBetween(1, 100);

        if ($roll <= 65) {
            $title = fake()->randomElement(self::PROJECT_TITLES);

            if (fake()->boolean(35)) {
                $title .= ' '.fake()->randomElement(self::PROJECT_SUFFIXES);
            }

            return $title;
        }

        if ($roll <= 85) {
            return sprintf(
                '%s %s',
                fake()->randomElement(self::STREETS),
                fake()->randomElement(self::PROJECT_TYPES),
            );
        }

        return sprintf(
            '%s %s',
            fake()->randomElement(self::COLORS),
            fake()->randomElement(self::PROJECT_TYPES),
        );
    }

    private static function buildTaskTitle(): string
    {
        $roll = fake()->numberBetween(1, 100);

        if ($roll <= 12) {
            return sprintf(
                '%s %s %s',
                fake()->randomElement(self::COLORS),
                fake()->randomElement(self::ANIMALS),
                fake()->randomElement(self::FIRST_NAMES),
            );
        }

        if ($roll <= 22) {
            return sprintf(
                '%s %s',
                fake()->randomElement(self::STREETS),
                fake()->randomElement(self::TASK_ACTIONS),
            );
        }

        if ($roll <= 30) {
            return sprintf(
                '%s rollout prep',
                fake()->randomElement(self::COUNTRIES),
            );
        }

        return sprintf(
            '%s %s',
            fake()->randomElement(self::TASK_ACTIONS),
            fake()->randomElement(self::TASK_OBJECTS),
        );
    }

    private static function buildChecklistTitle(): string
    {
        return sprintf(
            '%s checklist',
            fake()->randomElement([
                'Launch readiness',
                'Release review',
                'Client handoff',
                'QA pass',
                'Content migration',
                'Security follow-up',
                'Onboarding',
                'Deployment',
                'Training prep',
                'Retrospective',
            ]),
        );
    }
}
