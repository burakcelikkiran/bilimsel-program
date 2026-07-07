const DEFAULT_SESSION_STYLE = {
    borderColor: '#64748b',
    headerBg: 'bg-slate-100',
    badge: 'bg-slate-200 text-slate-800',
    timeBadge: 'bg-white text-slate-800 ring-slate-200',
    iconColor: 'text-slate-500',
    titleColor: 'text-slate-900',
    headerTextColor: 'text-slate-600',
    presentationContainerBg: 'bg-slate-50/60',
    isCompact: false,
    cardBorder: 'border-slate-200/80',
};

const SESSION_TYPE_STYLES = {
    plenary: {
        borderColor: '#7c3aed',
        headerBg: 'bg-violet-100',
        badge: 'bg-violet-200 text-violet-900',
        timeBadge: 'bg-white text-violet-900 ring-violet-200',
        iconColor: 'text-violet-600',
        titleColor: 'text-violet-950',
        headerTextColor: 'text-violet-800',
        presentationContainerBg: 'bg-violet-50/50',
        isCompact: false,
        cardBorder: 'border-violet-200/60',
    },
    main: {
        borderColor: '#4338ca',
        headerBg: 'bg-indigo-100',
        badge: 'bg-indigo-200 text-indigo-900',
        timeBadge: 'bg-white text-indigo-900 ring-indigo-200',
        iconColor: 'text-indigo-600',
        titleColor: 'text-indigo-950',
        headerTextColor: 'text-indigo-800',
        presentationContainerBg: 'bg-indigo-50/50',
        isCompact: false,
        cardBorder: 'border-indigo-200/60',
    },
    parallel: {
        borderColor: '#2563eb',
        headerBg: 'bg-blue-100',
        badge: 'bg-blue-200 text-blue-900',
        timeBadge: 'bg-white text-blue-900 ring-blue-200',
        iconColor: 'text-blue-600',
        titleColor: 'text-blue-950',
        headerTextColor: 'text-blue-800',
        presentationContainerBg: 'bg-blue-50/50',
        isCompact: false,
        cardBorder: 'border-blue-200/60',
    },
    oral_presentation: {
        borderColor: '#0891b2',
        headerBg: 'bg-cyan-100',
        badge: 'bg-cyan-200 text-cyan-900',
        timeBadge: 'bg-white text-cyan-900 ring-cyan-200',
        iconColor: 'text-cyan-600',
        titleColor: 'text-cyan-950',
        headerTextColor: 'text-cyan-800',
        presentationContainerBg: 'bg-cyan-50/50',
        isCompact: false,
        cardBorder: 'border-cyan-200/60',
    },
    workshop: {
        borderColor: '#16a34a',
        headerBg: 'bg-green-100',
        badge: 'bg-green-200 text-green-900',
        timeBadge: 'bg-white text-green-900 ring-green-200',
        iconColor: 'text-green-600',
        titleColor: 'text-green-950',
        headerTextColor: 'text-green-800',
        presentationContainerBg: 'bg-green-50/50',
        isCompact: false,
        cardBorder: 'border-green-200/60',
    },
    satellite: {
        borderColor: '#ea580c',
        headerBg: 'bg-orange-100',
        badge: 'bg-orange-200 text-orange-900',
        timeBadge: 'bg-white text-orange-900 ring-orange-200',
        iconColor: 'text-orange-600',
        titleColor: 'text-orange-950',
        headerTextColor: 'text-orange-800',
        presentationContainerBg: 'bg-orange-50/50',
        isCompact: false,
        cardBorder: 'border-orange-200/60',
    },
    special: {
        borderColor: '#9333ea',
        headerBg: 'bg-purple-100',
        badge: 'bg-purple-200 text-purple-900',
        timeBadge: 'bg-white text-purple-900 ring-purple-200',
        iconColor: 'text-purple-600',
        titleColor: 'text-purple-950',
        headerTextColor: 'text-purple-800',
        presentationContainerBg: 'bg-purple-50/50',
        isCompact: false,
        cardBorder: 'border-purple-200/60',
    },
    poster: {
        borderColor: '#ca8a04',
        headerBg: 'bg-yellow-100',
        badge: 'bg-yellow-200 text-yellow-900',
        timeBadge: 'bg-white text-yellow-900 ring-yellow-200',
        iconColor: 'text-yellow-700',
        titleColor: 'text-yellow-950',
        headerTextColor: 'text-yellow-800',
        presentationContainerBg: 'bg-yellow-50/50',
        isCompact: false,
        cardBorder: 'border-yellow-200/60',
    },
    social: {
        borderColor: '#db2777',
        headerBg: 'bg-pink-100',
        badge: 'bg-pink-200 text-pink-900',
        timeBadge: 'bg-white text-pink-900 ring-pink-200',
        iconColor: 'text-pink-600',
        titleColor: 'text-pink-950',
        headerTextColor: 'text-pink-800',
        presentationContainerBg: 'bg-pink-50/50',
        isCompact: false,
        cardBorder: 'border-pink-200/60',
    },
    break: {
        borderColor: '#b91c1c',
        headerBg: 'bg-red-600',
        badge: 'bg-red-500 text-white ring-1 ring-red-400',
        timeBadge: 'bg-red-500 text-white ring-red-400',
        iconColor: 'text-red-100',
        titleColor: 'text-white',
        headerTextColor: 'text-red-100',
        presentationContainerBg: 'bg-red-50/40',
        isCompact: true,
        cardBorder: 'border-red-300/60',
    },
    lunch: {
        borderColor: '#b91c1c',
        headerBg: 'bg-red-600',
        badge: 'bg-red-500 text-white ring-1 ring-red-400',
        timeBadge: 'bg-red-500 text-white ring-red-400',
        iconColor: 'text-red-100',
        titleColor: 'text-white',
        headerTextColor: 'text-red-100',
        presentationContainerBg: 'bg-red-50/40',
        isCompact: true,
        cardBorder: 'border-red-300/60',
    },
};

const DEFAULT_PRESENTATION_STYLE = {
    borderColor: '#64748b',
    rowBg: 'bg-white',
    rowBgAlt: 'bg-slate-50',
    badge: 'bg-slate-200 text-slate-800',
    iconColor: 'text-slate-500',
    label: 'Sunum',
};

const PRESENTATION_TYPE_STYLES = {
    keynote: {
        borderColor: '#9333ea',
        rowBg: 'bg-purple-50',
        rowBgAlt: 'bg-purple-100/60',
        badge: 'bg-purple-200 text-purple-900 font-semibold',
        iconColor: 'text-purple-600',
        label: 'Keynote',
    },
    oral: {
        borderColor: '#2563eb',
        rowBg: 'bg-blue-50',
        rowBgAlt: 'bg-blue-100/60',
        badge: 'bg-blue-200 text-blue-900 font-semibold',
        iconColor: 'text-blue-600',
        label: 'Sözlü',
    },
    poster: {
        borderColor: '#16a34a',
        rowBg: 'bg-green-50',
        rowBgAlt: 'bg-green-100/60',
        badge: 'bg-green-200 text-green-900 font-semibold',
        iconColor: 'text-green-600',
        label: 'Poster',
    },
    panel: {
        borderColor: '#ea580c',
        rowBg: 'bg-orange-50',
        rowBgAlt: 'bg-orange-100/60',
        badge: 'bg-orange-200 text-orange-900 font-semibold',
        iconColor: 'text-orange-600',
        label: 'Panel',
    },
    workshop: {
        borderColor: '#4f46e5',
        rowBg: 'bg-indigo-50',
        rowBgAlt: 'bg-indigo-100/60',
        badge: 'bg-indigo-200 text-indigo-900 font-semibold',
        iconColor: 'text-indigo-600',
        label: 'Workshop',
    },
};

export const SESSION_LEGEND_ITEMS = [
    { key: 'main', label: 'Ana Oturum', color: '#4338ca' },
    { key: 'plenary', label: 'Genel Oturum', color: '#7c3aed' },
    { key: 'parallel', label: 'Paralel Oturum', color: '#2563eb' },
    { key: 'oral_presentation', label: 'Sözlü Bildiri', color: '#0891b2' },
    { key: 'satellite', label: 'Uydu Sempozyumu', color: '#ea580c' },
    { key: 'special', label: 'Özel Oturum', color: '#9333ea' },
    { key: 'workshop', label: 'Workshop', color: '#16a34a' },
    { key: 'break', label: 'Ara', color: '#dc2626' },
    { key: 'lunch', label: 'Öğle Arası', color: '#dc2626' },
];

export const PRESENTATION_LEGEND_ITEMS = [
    { key: 'keynote', label: 'Keynote', color: '#9333ea' },
    { key: 'oral', label: 'Sözlü', color: '#2563eb' },
    { key: 'panel', label: 'Panel', color: '#ea580c' },
    { key: 'workshop', label: 'Workshop', color: '#4f46e5' },
    { key: 'poster', label: 'Poster', color: '#16a34a' },
];

export function getSessionTypeStyle(session) {
    const sessionType = session?.session_type;

    if (sessionType && SESSION_TYPE_STYLES[sessionType]) {
        return SESSION_TYPE_STYLES[sessionType];
    }

    if (session?.is_break) {
        return SESSION_TYPE_STYLES.break;
    }

    return DEFAULT_SESSION_STYLE;
}

export function getPresentationTypeStyle(presentationType) {
    return PRESENTATION_TYPE_STYLES[presentationType] ?? DEFAULT_PRESENTATION_STYLE;
}

export function getPresentationTypeLabel(presentationType) {
    return PRESENTATION_TYPE_STYLES[presentationType]?.label ?? presentationType ?? 'Sunum';
}

export function isCompactSession(session) {
    return getSessionTypeStyle(session).isCompact === true;
}
