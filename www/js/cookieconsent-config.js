import 'https://cdn.jsdelivr.net/gh/orestbida/cookieconsent@v3.0.0/dist/cookieconsent.umd.js';

CookieConsent.run({
    categories: {
        necessary: {
            enabled: true,
            readOnly: true
        },
        analytics: {}
    },

    language: {
        default: GLOB_LANG,
        translations: {
            cs: {
                consentModal: {
                    title: 'Používáme soubory cookies',
                    description: 'Na našich webových stránkách používáme soubory cookies. Některé z nich jsou nezbytné pro správné fungování webu, zatímco jiné nám pomáhají vylepšovat a optimalizovat uživatelské prostředí.',
                    acceptAllBtn: 'Souhlasím',
                    acceptNecessaryBtn: 'Odmítám',
                    showPreferencesBtn: 'Upravit mé předvolby'
                },
                preferencesModal: {
                    title: 'Nastavení cookies',
                    acceptAllBtn: 'Přijmout vše',
                    acceptNecessaryBtn: 'Odmítnout vše',
                    savePreferencesBtn: 'Uložit nastavení',
                    closeIconLabel: 'Zavřít',
                    sections: [
                        {
                            title: 'Použití cookies 📢',
                            description: 'Soubory cookies jsou velmi malé textové soubory, které se ukládají do vašeho zařízení při navštěvování webových stránek. Při procházení našich webových stránek můžete změnit své předvolby a odmítnout určité typy cookies, které se mají ukládat do vašeho počítače. Můžete také odstranit všechny soubory cookie, které jsou již uloženy ve vašem počítači. Pro více informací navštivte naši stránku zásady ochrany osobních údajů.'
                        },
                        {
                            title: 'Bezpodmínečně nutné soubory cookies',
                            description: 'Tyto soubory cookies jsou nezbytné k tomu, abychom vám mohli poskytovat služby dostupné prostřednictvím našeho webu. Bez těchto cookies nebude web fungovat správně.',
                            linkedCategory: 'necessary'
                        },
                        {
                            title: 'Analytické soubory cookies',
                            description: 'Tyto soubory cookies se používají ke shromažďování informací pro analýzu provozu na našich webových stránkách a sledování používání našich webových stránek uživateli. Informace shromážděné prostřednictvím těchto sledovacích a výkonnostních cookies neidentifikují žádné osoby.',
                            linkedCategory: 'analytics'
                        },
                        {
                            title: 'Další informace',
                            description: 'V případě jakýchkoliv dotazů ohledně našich zásad týkajících se souborů cookie a vašich možností nás prosím kontaktujte.'
                        }
                    ]
                }
            },
            en: {
                consentModal: {
                    title: 'We use cookies',
                    description: 'This website uses essential cookies to ensure its proper operation and tracking cookies to understand how you interact with it. The latter will be set only after consent.',
                    acceptAllBtn: 'Accept all',
                    acceptNecessaryBtn: 'Reject all',
                    showPreferencesBtn: 'Let me choose'
                },
                preferencesModal: {
                    title: 'Manage cookie preferences',
                    acceptAllBtn: 'Accept all',
                    acceptNecessaryBtn: 'Reject all',
                    savePreferencesBtn: 'Save settings',
                    closeIconLabel: 'Close',
                    sections: [
                        {
                            title: 'Cookie usage 📢',
                            description: 'Cookies are very small text files that are stored on your computer when you visit a website. We use cookies to ensure the basic functionalities of the website and to enhance your online experience. You can choose for each category to opt-in/out whenever you want. For more details relative to cookies and other sensitive data, please read the full privacy policy.'
                        },
                        {
                            title: 'Strictly Necessary cookies',
                            description: 'These cookies are essential for the proper functioning of my website. Without these cookies, the website would not work properly.',
                            linkedCategory: 'necessary'
                        },
                        {
                            title: 'Performance and Analytics cookies',
                            description: 'These cookies are used to collect information to analyze the traffic to our website and how visitors are using our website. The information collected through these tracking and performance cookies do not identify any individual visitor.',
                            linkedCategory: 'analytics'
                        },
                        {
                            title: 'More information',
                            description: 'For any queries in relation to my policy on cookies and your choices, please contact us.'
                        }
                    ]
                }
            }
        }
    }
});
