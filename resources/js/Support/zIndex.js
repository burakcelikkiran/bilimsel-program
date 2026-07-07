/**
 * Uygulama genelinde tutarlı katman sırası.
 * Değerler 1000'i geçmez; modal/toast gibi yeni katmanlar için 10'ar artırılabilir.
 *
 * AdminLayout referansı: sidebar 40, header 30
 */
export const Z_INDEX = {
    elevated: 10,
    dropdown: 50,
    modalBackdrop: 60,
    modal: 70,
    popover: 80,
    toast: 90,
}
