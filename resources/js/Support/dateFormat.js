const TURKISH_MONTHS = {
    1: 'Ocak',
    2: 'Şubat',
    3: 'Mart',
    4: 'Nisan',
    5: 'Mayıs',
    6: 'Haziran',
    7: 'Temmuz',
    8: 'Ağustos',
    9: 'Eylül',
    10: 'Ekim',
    11: 'Kasım',
    12: 'Aralık',
};

function parseDateValue(value) {
    if (!value) {
        return null;
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return null;
    }

    return date;
}

export function formatEventDate(value) {
    const date = parseDateValue(value);

    if (!date) {
        return value ?? null;
    }

    const day = date.getDate();
    const month = TURKISH_MONTHS[date.getMonth() + 1];
    const year = date.getFullYear();

    return `${day} ${month} ${year}`;
}

export function formatEventDateRange(startDate, endDate) {
    if (!startDate) {
        return null;
    }

    const formattedStart = formatEventDate(startDate);

    if (!endDate || startDate === endDate) {
        return formattedStart;
    }

    const formattedEnd = formatEventDate(endDate);

    return `${formattedStart} — ${formattedEnd}`;
}
