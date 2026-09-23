export const moduleVisual: Record<string, { icon: string; chip: string }> = {
    schemes: { icon: '🏛', chip: 'Scheme desk' },
    scholarships: { icon: '🎓', chip: 'Education grant' },
    jobs: { icon: '💼', chip: 'Livelihood' },
    education: { icon: '📚', chip: 'Study support' },
    health: { icon: '🩺', chip: 'Camp' },
    blood: { icon: '🩸', chip: 'Emergency' },
    mock_tests: { icon: '📝', chip: 'Exam prep' },
    sakhi: { icon: '🤝', chip: 'Women circle' },
    village_reports: { icon: '📍', chip: 'Village note' },
    mentorship: { icon: '⚖️', chip: 'Guidance' },
    entrepreneurship: { icon: '🌱', chip: 'Livelihood' },
    volunteer: { icon: '🙌', chip: 'Join desk' },
};

export function visualFor(slug: string) {
    return moduleVisual[slug] ?? { icon: '✦', chip: 'Service' };
}
