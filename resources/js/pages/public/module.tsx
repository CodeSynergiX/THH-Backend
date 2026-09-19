import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import {
    Activity,
    ArrowLeft,
    BookOpen,
    Briefcase,
    CheckCircle2,
    ChevronRight,
    Copy,
    Droplet,
    GraduationCap,
    HeartHandshake,
    Landmark,
    Moon,
    Search,
    Send,
    Shield,
    Sun,
    Users,
} from 'lucide-react';
import { useTranslation } from '../../lib/i18n';

interface Taluka {
    id: number;
    name_en: string;
    name_gu?: string;
    villages?: Array<{ id: number; name_en: string; name_gu?: string }>;
}

interface District {
    id: number;
    name_en: string;
    name_gu?: string;
    talukas?: Taluka[];
}

interface PublicModuleProps {
    module: string;
    items: any[];
    districts: District[];
    categories: any[];
}

export default function PublicModule({
    module,
    items,
    districts,
}: PublicModuleProps) {
    const { locale, switchLocale } = useTranslation();
    const isGu = locale === 'gu';

    const [isDark, setIsDark] = useState(false);
    const [search, setSearch] = useState('');
    const [copied, setCopied] = useState(false);

    // Form state
    const { data, setData, post, processing, errors, reset } = useForm({
        beneficiary_name: '',
        beneficiary_phone: '',
        title: '',
        description: '',
        district_id: '',
        taluka_id: '',
        village_id: '',
        urgency: 'normal',
    });

    const [createdCaseNo, setCreatedCaseNo] = useState<string | null>(null);

    const toggleTheme = () => {
        setIsDark(!isDark);
        document.documentElement.classList.toggle('dark');
    };

    // Module definitions
    const metaMap: Record<
        string,
        {
            title: string;
            titleGu: string;
            desc: string;
            descGu: string;
            icon: React.ReactNode;
            badgeColor: string;
        }
    > = {
        schemes: {
            title: 'Government Schemes & Welfare',
            titleGu: 'સરકારી કલ્યાણકારી યોજનાઓ',
            desc: 'Direct financial assistance, tribal housing grants, solar agriculture pumps, and forest rights (FRA).',
            descGu: 'સરકારી યોજનાઓ — આવાસ સહાય, સોલાર પંપ અને જંગલ હક્ક લાભો.',
            icon: <Landmark className="h-8 w-8 text-amber-600" />,
            badgeColor: 'bg-amber-500/10 text-amber-700 border-amber-500/30',
        },
        scholarships: {
            title: 'Scholarships & Higher Education',
            titleGu: 'શિષ્યવૃત્તિ અને ઉચ્ચ શિક્ષણ',
            desc: 'Post-Matric scholarships, technical coaching grants, hostel admissions, and overseas study support.',
            descGu: 'પોસ્ટ-મેટ્રિક શિષ્યવૃત્તિ, કોલેજ સહાય અને આદર્શ છાત્રાલય પ્રવેશ.',
            icon: <GraduationCap className="h-8 w-8 text-blue-600" />,
            badgeColor: 'bg-blue-500/10 text-blue-700 border-blue-500/30',
        },
        jobs: {
            title: 'Job Vacancies & Skill Training',
            titleGu: 'રોજગાર તકો અને કૌશલ્ય તાલીમ',
            desc: 'Verified government apprenticeships, private job fairs, and vocational training across Gujarat.',
            descGu: 'સરકારી અને અર્ધ-સરકારી ભરતી, એપ્રેન્ટિસશીપ અને કૌશલ્ય વિકાસ.',
            icon: <Briefcase className="h-8 w-8 text-emerald-600" />,
            badgeColor:
                'bg-emerald-500/10 text-emerald-700 border-emerald-500/30',
        },
        health: {
            title: 'Healthcare & Medical Camps',
            titleGu: 'આરોગ્ય સહાય અને મેડિકલ કેમ્પ',
            desc: 'Free health diagnostic camps, sickle cell anemia checkups, and specialized tribal treatment funds.',
            descGu: 'મફત મેડિકલ કેમ્પ, સિકલ સેલ એનિમિયા સારવાર અને આરોગ્ય રક્ષણ.',
            icon: <Activity className="h-8 w-8 text-rose-600" />,
            badgeColor: 'bg-rose-500/10 text-rose-700 border-rose-500/30',
        },
        blood: {
            title: 'Emergency Blood Donors & Requests',
            titleGu: 'ઇમરજન્સી રક્ત સહાય અને રક્તદાન',
            desc: '24x7 verified blood availability, donor network matching, and rapid response for rural hospitals.',
            descGu: 'તાત્કાલિક રક્ત સહાય અને રક્તદાતા નેટવર્ક જોડાણ.',
            icon: <Droplet className="h-8 w-8 text-red-600" />,
            badgeColor: 'bg-red-500/10 text-red-700 border-red-500/30',
        },
        mock_tests: {
            title: 'GPSC & Police Exam Preparation',
            titleGu: 'સ્પર્ધાત્મક પરીક્ષા તૈયારી અને મોક ટેસ્ટ',
            desc: 'Practice papers, bilingual study material, syllabus guides, and live mentor question sessions.',
            descGu: 'જીપીએસસી અને પોલીસ કોન્સ્ટેબલ પરીક્ષા તૈયારી અને પુસ્તકો.',
            icon: <BookOpen className="h-8 w-8 text-indigo-600" />,
            badgeColor: 'bg-indigo-500/10 text-indigo-700 border-indigo-500/30',
        },
        sakhi: {
            title: 'Sakhi Circles & Women Empowerment',
            titleGu: 'સખી મંડળ પ્રવૃત્તિ અને મહિલા સશક્તિકરણ',
            desc: 'Self-Help Groups (SHGs), micro-finance loans, handicraft artisan sales, and leadership workshops.',
            descGu: 'સ્વ-સહાય જૂથો, ગૃહઉદ્યોગ લોન અને આદિવાસી મહિલા નેતૃત્વ.',
            icon: <Users className="h-8 w-8 text-pink-600" />,
            badgeColor: 'bg-pink-500/10 text-pink-700 border-pink-500/30',
        },
        village_reports: {
            title: 'Village Infrastructure Issues',
            titleGu: 'ગ્રામીણ સમસ્યાઓ અને ફરિયાદ નિવારણ',
            desc: 'Report drinking water faults, broken road connectivity, power cuts, or school infrastructure needs.',
            descGu: 'પીવાનું પાણી, રસ્તા અને વીજળી ફરિયાદ સીધી ટ્રસ્ટના સ્વયંસેવકો સુધી પહોંચાડો.',
            icon: <Shield className="h-8 w-8 text-orange-600" />,
            badgeColor: 'bg-orange-500/10 text-orange-700 border-orange-500/30',
        },
    };

    const currentMeta = metaMap[module] || metaMap.schemes;

    // Filtered items
    const filteredItems = items.filter((it: any) => {
        const title = (
            it.title ||
            it.title_gu ||
            it.title_en ||
            it.name ||
            it.patient_name ||
            ''
        ).toLowerCase();
        const desc = (
            it.description ||
            it.description_gu ||
            it.description_en ||
            it.location ||
            ''
        ).toLowerCase();
        const q = search.toLowerCase();
        return title.includes(q) || desc.includes(q);
    });

    // Selected District Talukas
    const selectedDistrictObj = districts.find(
        (d) => String(d.id) === String(data.district_id),
    );
    const availableTalukas = selectedDistrictObj?.talukas || [];
    const selectedTalukaObj = availableTalukas.find(
        (t) => String(t.id) === String(data.taluka_id),
    );
    const availableVillages = selectedTalukaObj?.villages || [];

    const handleSubmitApplication = (e: React.FormEvent) => {
        e.preventDefault();
        post(`/community/${module}/apply`, {
            preserveScroll: true,
            onSuccess: (page: any) => {
                const caseNo = page.props?.flash?.success_case;
                if (caseNo) {
                    setCreatedCaseNo(caseNo);
                } else {
                    setCreatedCaseNo(
                        `THH-${new Date().getFullYear()}-${Math.floor(10000 + Math.random() * 90000)}`,
                    );
                }
                reset('title', 'description');
            },
        });
    };

    const handleCopyCase = () => {
        if (createdCaseNo) {
            void navigator.clipboard.writeText(createdCaseNo);
            setCopied(true);
            setTimeout(() => setCopied(false), 2000);
        }
    };

    return (
        <div className="bg-thh-bg text-thh-text min-h-screen transition-colors duration-300">
            <Head
                title={`${isGu ? currentMeta.titleGu : currentMeta.title} | Tribal Helping Hand`}
            />

            {/* Top Navigation */}
            <header className="border-thh-border bg-thh-surface/90 sticky top-0 z-40 border-b backdrop-blur-md">
                <div className="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6">
                    <div className="flex items-center gap-3">
                        <Link
                            href="/"
                            className="bg-thh-bg border-thh-border text-thh-text hover:border-thh-primary flex h-9 w-9 items-center justify-center rounded-xl border transition-all"
                        >
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                        <div>
                            <div className="flex items-center gap-2">
                                <span className="bg-thh-primary rounded px-1.5 py-0.5 text-[10px] font-black tracking-wider text-white uppercase">
                                    GGVT TRUST
                                </span>
                                <h1 className="text-thh-text text-sm font-extrabold sm:text-base">
                                    {isGu
                                        ? currentMeta.titleGu
                                        : currentMeta.title}
                                </h1>
                            </div>
                            <p className="text-thh-text-muted text-[11px]">
                                {isGu
                                    ? 'ગ્લોબલ ગ્રામીણ વિકાસ ટ્રસ્ટ — જનસેવા પોર્ટલ'
                                    : 'Global Gramin Vikas Trust — Citizen Portal'}
                            </p>
                        </div>
                    </div>

                    <div className="flex items-center gap-2">
                        {/* Language Switcher Pill */}
                        <div className="bg-thh-bg border-thh-border flex items-center rounded-full border p-1 text-xs font-bold">
                            <button
                                type="button"
                                onClick={() => switchLocale('en')}
                                className={`rounded-full px-2.5 py-1 transition-all ${
                                    locale === 'en'
                                        ? 'bg-thh-primary text-white shadow-xs'
                                        : 'text-thh-text-muted hover:text-thh-text'
                                }`}
                            >
                                EN
                            </button>
                            <button
                                type="button"
                                onClick={() => switchLocale('gu')}
                                className={`rounded-full px-2.5 py-1 transition-all ${
                                    locale === 'gu'
                                        ? 'bg-thh-primary text-white shadow-xs'
                                        : 'text-thh-text-muted hover:text-thh-text'
                                }`}
                            >
                                ગુજરાતી
                            </button>
                        </div>

                        {/* Theme Toggle */}
                        <button
                            type="button"
                            onClick={toggleTheme}
                            className="bg-thh-bg border-thh-border text-thh-text hover:border-thh-primary flex h-9 w-9 items-center justify-center rounded-full border transition-all"
                            aria-label="Toggle Theme"
                        >
                            {isDark ? (
                                <Sun className="h-4 w-4 text-amber-500" />
                            ) : (
                                <Moon className="h-4 w-4 text-slate-700 dark:text-slate-300" />
                            )}
                        </button>
                    </div>
                </div>
            </header>

            <main className="mx-auto max-w-7xl space-y-10 px-4 py-8 sm:px-6">
                {/* Hero Banner with Details */}
                <div className="bg-thh-surface border-thh-border relative overflow-hidden rounded-3xl border p-6 shadow-sm sm:p-10">
                    <div className="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                        <div className="flex items-start gap-4">
                            <div className="bg-thh-bg border-thh-border flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl border shadow-inner">
                                {currentMeta.icon}
                            </div>
                            <div className="space-y-1">
                                <span
                                    className={`inline-flex items-center rounded-full border px-3 py-0.5 text-xs font-bold ${currentMeta.badgeColor}`}
                                >
                                    {isGu ? 'સક્રિય કેટેગરી' : 'Community Module'}
                                </span>
                                <h2 className="text-thh-text text-2xl font-black sm:text-3xl">
                                    {isGu
                                        ? currentMeta.titleGu
                                        : currentMeta.title}
                                </h2>
                                <p className="text-thh-text-muted text-xs leading-relaxed sm:text-sm">
                                    {isGu
                                        ? currentMeta.descGu
                                        : currentMeta.desc}
                                </p>
                            </div>
                        </div>

                        <a
                            href="#help-form"
                            className="bg-thh-primary inline-flex shrink-0 items-center justify-center gap-2 rounded-2xl px-6 py-3 text-sm font-bold text-white shadow-md transition-all hover:opacity-95"
                        >
                            <Send className="h-4 w-4" />
                            <span>
                                {isGu
                                    ? 'સહાય માટે અરજી કરો ↓'
                                    : 'Get Help / Apply ↓'}
                            </span>
                        </a>
                    </div>
                </div>

                {/* Directory / Listing Section */}
                <section className="space-y-6">
                    <div className="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                        <div>
                            <h3 className="text-thh-text text-xl font-black tracking-tight">
                                {isGu
                                    ? 'ઉપલબ્ધ તકો અને યાદી'
                                    : 'Available Opportunities & Programs'}
                            </h3>
                            <p className="text-thh-text-muted text-xs">
                                {filteredItems.length}{' '}
                                {isGu ? 'રેકોર્ડ મળ્યા' : 'records found'}
                            </p>
                        </div>

                        {/* Search Input */}
                        <div className="relative w-full sm:w-80">
                            <Search className="text-thh-text-muted absolute top-3 left-3 h-4 w-4" />
                            <input
                                type="text"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder={
                                    isGu
                                        ? 'શોધો (નામ, વિગત)...'
                                        : 'Search listings...'
                                }
                                className="bg-thh-surface border-thh-border text-thh-text placeholder:text-thh-text-muted focus:border-thh-primary w-full rounded-xl border py-2 pr-4 pl-9 text-xs font-medium focus:outline-hidden"
                            />
                        </div>
                    </div>

                    {filteredItems.length === 0 ? (
                        <div className="bg-thh-surface border-thh-border text-thh-text-muted rounded-2xl border py-12 text-center text-sm">
                            {isGu
                                ? 'હાલમાં કોઈ રેકોર્ડ ઉપલબ્ધ નથી. કૃપા કરીને નીચે આપેલા ફોર્મ દ્વારા તમારી અરજી નોંધાવો.'
                                : 'No listings currently found. You can submit an assistance request using the form below.'}
                        </div>
                    ) : (
                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            {filteredItems.map((item: any) => {
                                const title =
                                    item.title_gu ||
                                    item.title ||
                                    item.title_en ||
                                    item.patient_name ||
                                    item.name ||
                                    'Welfare Listing';
                                const desc =
                                    item.description_gu ||
                                    item.description ||
                                    item.description_en ||
                                    item.hospital ||
                                    item.location ||
                                    '';
                                const subBadge =
                                    item.urgency ||
                                    item.category?.slug ||
                                    item.blood_group ||
                                    item.stipend ||
                                    'Active';

                                return (
                                    <div
                                        key={item.id}
                                        className="bg-thh-surface border-thh-border hover:border-thh-primary group flex flex-col justify-between rounded-2xl border p-5 shadow-xs transition-all hover:shadow-md"
                                    >
                                        <div className="space-y-2">
                                            <div className="flex items-center justify-between">
                                                <span className="bg-thh-secondary/10 text-thh-secondary border-thh-secondary/30 rounded-md border px-2 py-0.5 text-[10px] font-bold uppercase">
                                                    {subBadge}
                                                </span>
                                                <span className="text-thh-text-muted text-[10px]">
                                                    {item.created_at
                                                        ? new Date(
                                                              item.created_at,
                                                          ).toLocaleDateString()
                                                        : 'Active'}
                                                </span>
                                            </div>

                                            <h4 className="text-thh-text text-sm font-bold sm:text-base">
                                                {title}
                                            </h4>
                                            <p className="text-thh-text-muted line-clamp-3 text-xs leading-relaxed">
                                                {desc}
                                            </p>
                                        </div>

                                        <div className="border-thh-border mt-4 border-t pt-3">
                                            <a
                                                href="#help-form"
                                                onClick={() => {
                                                    setData(
                                                        'title',
                                                        `Inquiry / Application for: ${title}`,
                                                    );
                                                }}
                                                className="text-thh-primary inline-flex items-center gap-1.5 text-xs font-bold hover:underline"
                                            >
                                                <span>
                                                    {isGu
                                                        ? 'આ માટે સહાય માંગો'
                                                        : 'Apply for this'}
                                                </span>
                                                <ChevronRight className="h-3.5 w-3.5" />
                                            </a>
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    )}
                </section>

                {/* Assistance Form Below That (User Request) */}
                <section id="help-form" className="scroll-mt-20 pt-6">
                    <div className="bg-thh-surface border-thh-border overflow-hidden rounded-3xl border shadow-md">
                        <div className="bg-thh-primary/10 border-thh-border border-b p-6 sm:p-8">
                            <div className="flex items-center gap-3">
                                <div className="bg-thh-primary flex h-10 w-10 items-center justify-center rounded-xl text-white">
                                    <HeartHandshake className="h-5 w-5" />
                                </div>
                                <div>
                                    <h3 className="text-thh-text text-lg font-black sm:text-xl">
                                        {isGu
                                            ? 'સહાય મેળવવા માટે અરજી કરો (Get Help Form)'
                                            : 'Submit Help & Assistance Application'}
                                    </h3>
                                    <p className="text-thh-text-muted text-xs">
                                        {isGu
                                            ? 'તમારી વિગતો ભરો અને ૨૪ કલાકમાં સહાય માર્ગદર્શન મેળવો'
                                            : 'Fill in your details to register a grassroots support request.'}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div className="p-6 sm:p-8">
                            {/* Confirmation Card if newly created */}
                            {createdCaseNo ? (
                                <div className="space-y-4 rounded-2xl border border-emerald-500/30 bg-emerald-500/10 p-6 text-center">
                                    <CheckCircle2 className="mx-auto h-12 w-12 text-emerald-600" />
                                    <div className="space-y-1">
                                        <h4 className="text-base font-extrabold text-emerald-800 dark:text-emerald-200">
                                            {isGu
                                                ? 'અરજી સફળતાપૂર્વક નોંધાઈ ગઈ છે!'
                                                : 'Application Successfully Registered!'}
                                        </h4>
                                        <p className="text-xs text-emerald-700 dark:text-emerald-300">
                                            {isGu
                                                ? 'તમારો કેસ નંબર સુરક્ષિત રાખો:'
                                                : 'Your Tracking Case Number:'}
                                        </p>
                                    </div>

                                    <div className="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2 text-lg font-black text-slate-900 shadow-xs dark:bg-slate-900 dark:text-white">
                                        <span>{createdCaseNo}</span>
                                        <button
                                            type="button"
                                            onClick={handleCopyCase}
                                            className="text-thh-primary hover:opacity-80"
                                        >
                                            <Copy className="h-4 w-4" />
                                        </button>
                                        {copied && (
                                            <span className="text-xs font-bold text-emerald-600">
                                                Copied!
                                            </span>
                                        )}
                                    </div>

                                    <div className="pt-2">
                                        <Link
                                            href={`/?track=${createdCaseNo}`}
                                            className="bg-thh-primary inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-xs font-bold text-white shadow-xs"
                                        >
                                            <span>
                                                {isGu
                                                    ? 'સ્ટેટસ ટ્રેક કરો'
                                                    : 'Track Status'}
                                            </span>
                                            <ChevronRight className="h-4 w-4" />
                                        </Link>
                                    </div>
                                </div>
                            ) : (
                                <form
                                    onSubmit={handleSubmitApplication}
                                    className="space-y-6"
                                >
                                    <div className="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                        {/* Applicant Name */}
                                        <div className="space-y-1.5">
                                            <label className="text-thh-text text-xs font-bold">
                                                {isGu
                                                    ? 'અરજદારનું પૂરું નામ *'
                                                    : 'Applicant Full Name *'}
                                            </label>
                                            <input
                                                type="text"
                                                required
                                                value={data.beneficiary_name}
                                                onChange={(e) =>
                                                    setData(
                                                        'beneficiary_name',
                                                        e.target.value,
                                                    )
                                                }
                                                placeholder={
                                                    isGu
                                                        ? 'દા.ત. રમેશભાઈ રાઠવા'
                                                        : 'e.g. Ramesh Rathwa'
                                                }
                                                className="bg-thh-bg border-thh-border text-thh-text focus:border-thh-primary w-full rounded-xl border px-3.5 py-2.5 text-xs font-medium focus:outline-hidden"
                                            />
                                            {errors.beneficiary_name && (
                                                <p className="text-[11px] font-semibold text-rose-500">
                                                    {errors.beneficiary_name}
                                                </p>
                                            )}
                                        </div>

                                        {/* Phone Number */}
                                        <div className="space-y-1.5">
                                            <label className="text-thh-text text-xs font-bold">
                                                {isGu
                                                    ? 'મોબાઇલ નંબર *'
                                                    : 'Contact Phone Number *'}
                                            </label>
                                            <input
                                                type="tel"
                                                required
                                                value={data.beneficiary_phone}
                                                onChange={(e) =>
                                                    setData(
                                                        'beneficiary_phone',
                                                        e.target.value,
                                                    )
                                                }
                                                placeholder="9876543210"
                                                className="bg-thh-bg border-thh-border text-thh-text focus:border-thh-primary w-full rounded-xl border px-3.5 py-2.5 text-xs font-medium focus:outline-hidden"
                                            />
                                            {errors.beneficiary_phone && (
                                                <p className="text-[11px] font-semibold text-rose-500">
                                                    {errors.beneficiary_phone}
                                                </p>
                                            )}
                                        </div>
                                    </div>

                                    {/* Geographic Selectors */}
                                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                        <div className="space-y-1.5">
                                            <label className="text-thh-text text-xs font-bold">
                                                {isGu
                                                    ? 'જિલ્લો (District) *'
                                                    : 'District *'}
                                            </label>
                                            <select
                                                required
                                                value={data.district_id}
                                                onChange={(e) => {
                                                    setData(
                                                        'district_id',
                                                        e.target.value,
                                                    );
                                                    setData('taluka_id', '');
                                                    setData('village_id', '');
                                                }}
                                                className="bg-thh-bg border-thh-border text-thh-text focus:border-thh-primary w-full rounded-xl border px-3.5 py-2.5 text-xs font-medium focus:outline-hidden"
                                            >
                                                <option value="">
                                                    {isGu
                                                        ? '-- જિલ્લો પસંદ કરો --'
                                                        : '-- Select District --'}
                                                </option>
                                                {districts.map((d) => (
                                                    <option
                                                        key={d.id}
                                                        value={d.id}
                                                    >
                                                        {isGu && d.name_gu
                                                            ? d.name_gu
                                                            : d.name_en}
                                                    </option>
                                                ))}
                                            </select>
                                        </div>

                                        <div className="space-y-1.5">
                                            <label className="text-thh-text text-xs font-bold">
                                                {isGu
                                                    ? 'તાલુકો (Taluka)'
                                                    : 'Taluka'}
                                            </label>
                                            <select
                                                value={data.taluka_id}
                                                disabled={!data.district_id}
                                                onChange={(e) => {
                                                    setData(
                                                        'taluka_id',
                                                        e.target.value,
                                                    );
                                                    setData('village_id', '');
                                                }}
                                                className="bg-thh-bg border-thh-border text-thh-text focus:border-thh-primary w-full rounded-xl border px-3.5 py-2.5 text-xs font-medium focus:outline-hidden disabled:opacity-50"
                                            >
                                                <option value="">
                                                    {isGu
                                                        ? '-- તાલુકો પસંદ કરો --'
                                                        : '-- Select Taluka --'}
                                                </option>
                                                {availableTalukas.map((t) => (
                                                    <option
                                                        key={t.id}
                                                        value={t.id}
                                                    >
                                                        {isGu && t.name_gu
                                                            ? t.name_gu
                                                            : t.name_en}
                                                    </option>
                                                ))}
                                            </select>
                                        </div>

                                        <div className="space-y-1.5">
                                            <label className="text-thh-text text-xs font-bold">
                                                {isGu
                                                    ? 'ગામ (Village)'
                                                    : 'Village'}
                                            </label>
                                            <select
                                                value={data.village_id}
                                                disabled={!data.taluka_id}
                                                onChange={(e) =>
                                                    setData(
                                                        'village_id',
                                                        e.target.value,
                                                    )
                                                }
                                                className="bg-thh-bg border-thh-border text-thh-text focus:border-thh-primary w-full rounded-xl border px-3.5 py-2.5 text-xs font-medium focus:outline-hidden disabled:opacity-50"
                                            >
                                                <option value="">
                                                    {isGu
                                                        ? '-- ગામ પસંદ કરો --'
                                                        : '-- Select Village --'}
                                                </option>
                                                {availableVillages.map((v) => (
                                                    <option
                                                        key={v.id}
                                                        value={v.id}
                                                    >
                                                        {isGu && v.name_gu
                                                            ? v.name_gu
                                                            : v.name_en}
                                                    </option>
                                                ))}
                                            </select>
                                        </div>
                                    </div>

                                    {/* Title of Help */}
                                    <div className="space-y-1.5">
                                        <label className="text-thh-text text-xs font-bold">
                                            {isGu
                                                ? 'સહાયનો વિષય / શીર્ષક *'
                                                : 'Subject of Assistance Request *'}
                                        </label>
                                        <input
                                            type="text"
                                            required
                                            value={data.title}
                                            onChange={(e) =>
                                                setData('title', e.target.value)
                                            }
                                            placeholder={
                                                isGu
                                                    ? 'દા.ત. આવાસ યોજના ફોર્મ અને દસ્તાવેજ સહાય'
                                                    : 'e.g. Need assistance with Awas application'
                                            }
                                            className="bg-thh-bg border-thh-border text-thh-text focus:border-thh-primary w-full rounded-xl border px-3.5 py-2.5 text-xs font-medium focus:outline-hidden"
                                        />
                                        {errors.title && (
                                            <p className="text-[11px] font-semibold text-rose-500">
                                                {errors.title}
                                            </p>
                                        )}
                                    </div>

                                    {/* Description */}
                                    <div className="space-y-1.5">
                                        <label className="text-thh-text text-xs font-bold">
                                            {isGu
                                                ? 'સમસ્યા અથવા સહાયની વિગતવાર માહિતી *'
                                                : 'Detailed Description of Help Needed *'}
                                        </label>
                                        <textarea
                                            rows={4}
                                            required
                                            value={data.description}
                                            onChange={(e) =>
                                                setData(
                                                    'description',
                                                    e.target.value,
                                                )
                                            }
                                            placeholder={
                                                isGu
                                                    ? 'તમને શું મુશ્કેલી છે અથવા કયા દસ્તાવેજો ખૂટે છે તે લખો...'
                                                    : 'Provide specific details regarding your situation, documents available, or current issue...'
                                            }
                                            className="bg-thh-bg border-thh-border text-thh-text focus:border-thh-primary w-full rounded-xl border px-3.5 py-2.5 text-xs font-medium focus:outline-hidden"
                                        />
                                        {errors.description && (
                                            <p className="text-[11px] font-semibold text-rose-500">
                                                {errors.description}
                                            </p>
                                        )}
                                    </div>

                                    {/* Urgency selection */}
                                    <div className="space-y-1.5">
                                        <label className="text-thh-text text-xs font-bold">
                                            {isGu
                                                ? 'જરૂરિયાતનો પ્રકાર (Urgency)'
                                                : 'Urgency Level'}
                                        </label>
                                        <div className="flex flex-wrap gap-3">
                                            {[
                                                {
                                                    key: 'normal',
                                                    labelEn:
                                                        'Normal (5-7 days)',
                                                    labelGu:
                                                        'સામાન્ય (૫-૭ દિવસ)',
                                                },
                                                {
                                                    key: 'urgent',
                                                    labelEn:
                                                        'Urgent (48 hours)',
                                                    labelGu: 'જરૂરી (૪૮ કલાક)',
                                                },
                                                {
                                                    key: 'emergency',
                                                    labelEn:
                                                        'Critical / Emergency (SOS)',
                                                    labelGu:
                                                        'તાત્કાલિક / ઇમરજન્સી',
                                                },
                                            ].map((u) => (
                                                <button
                                                    key={u.key}
                                                    type="button"
                                                    onClick={() =>
                                                        setData(
                                                            'urgency',
                                                            u.key,
                                                        )
                                                    }
                                                    className={`rounded-xl border px-3.5 py-2 text-xs font-bold transition-all ${
                                                        data.urgency === u.key
                                                            ? 'border-thh-primary bg-thh-primary text-white shadow-xs'
                                                            : 'bg-thh-bg border-thh-border text-thh-text-muted hover:border-thh-primary'
                                                    }`}
                                                >
                                                    {isGu
                                                        ? u.labelGu
                                                        : u.labelEn}
                                                </button>
                                            ))}
                                        </div>
                                    </div>

                                    <div className="border-thh-border border-t pt-4">
                                        <button
                                            type="submit"
                                            disabled={processing}
                                            className="bg-thh-primary inline-flex w-full items-center justify-center gap-2 rounded-2xl px-6 py-3.5 text-sm font-bold text-white shadow-md transition-all hover:opacity-95 disabled:opacity-50 sm:w-auto"
                                        >
                                            <Send className="h-4 w-4" />
                                            <span>
                                                {processing
                                                    ? isGu
                                                        ? 'નોંધણી થઈ રહી છે...'
                                                        : 'Submitting Request...'
                                                    : isGu
                                                      ? 'અરજી જમા કરો (Submit Request)'
                                                      : 'Submit Assistance Request'}
                                            </span>
                                        </button>
                                    </div>
                                </form>
                            )}
                        </div>
                    </div>
                </section>
            </main>
        </div>
    );
}
