import React, { useState } from 'react';
import { Link, router, useForm } from '@inertiajs/react';
import {
    ArrowLeft,
    Plus,
    Search,
    CheckCircle,
    XCircle,
    X,
    Briefcase,
    GraduationCap,
    Landmark,
    Activity,
    Droplet,
    BookOpen,
    HeartHandshake,
    MapPin,
} from 'lucide-react';
import AdminLayout from '../../../components/AdminLayout';

interface ModuleMeta {
    slug: string;
    title: string;
    title_gu: string;
    description: string;
    singular: string;
}

interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    total: number;
    per_page: number;
    links: { url: string | null; label: string; active: boolean }[];
}

interface DistrictOption {
    id: number;
    name: string;
    code: string;
}

interface VillageOption {
    id: number;
    name: string;
}

interface HospitalOption {
    id: number;
    name: string;
}

interface ManageProps {
    meta: ModuleMeta;
    items: PaginatedData<any>;
    filters: { search?: string };
    districts: DistrictOption[];
    villages: VillageOption[];
    hospitals: HospitalOption[];
}

export default function ContentManage({
    meta,
    items,
    filters,
    districts,
    villages,
    hospitals,
}: ManageProps) {
    const [search, setSearch] = useState(filters.search || '');
    const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(
            `/admin/content/${meta.slug}`,
            { search },
            { preserveState: true },
        );
    };

    const handleToggle = (id: number) => {
        router.post(
            `/admin/content/${meta.slug}/${id}/toggle`,
            {},
            { preserveScroll: true },
        );
    };

    // Generic form for new record creation
    const { data, setData, post, processing, reset } = useForm<
        Record<string, any>
    >({
        title: '',
        slug: '',
        benefit_summary: '',
        documents: '',
        amount: '',
        deadline_days: '30',
        company: '',
        location: '',
        salary_range: '',
        requirements: '',
        organizer: '',
        district_id: districts[0]?.id || '',
        village_id: villages[0]?.id || '',
        address: '',
        scheduled_at: new Date().toISOString().slice(0, 16),
        patient_name: '',
        blood_group: 'O+',
        hospital_id: hospitals[0]?.id || '',
        units_required: '1',
        contact_phone: '',
        category: 'competitive_exam',
        duration_minutes: '60',
        total_marks: '100',
        name: '',
        leader_name: '',
        leader_phone: '',
        members_count: '10',
        description: '',
    });

    const submitCreate = (e: React.FormEvent) => {
        e.preventDefault();
        post(`/admin/content/${meta.slug}`, {
            onSuccess: () => {
                setIsCreateModalOpen(false);
                reset();
            },
        });
    };

    const getModuleIcon = (slug: string) => {
        switch (slug) {
            case 'schemes':
                return <Landmark className="text-thh-primary h-6 w-6" />;
            case 'scholarships':
                return <GraduationCap className="text-thh-secondary h-6 w-6" />;
            case 'jobs':
                return <Briefcase className="text-thh-accent h-6 w-6" />;
            case 'health':
                return <Activity className="h-6 w-6 text-rose-600" />;
            case 'blood':
                return <Droplet className="h-6 w-6 text-red-600" />;
            case 'mock_tests':
                return <BookOpen className="h-6 w-6 text-indigo-600" />;
            case 'sakhi':
                return <HeartHandshake className="h-6 w-6 text-teal-600" />;
            case 'village_reports':
                return <MapPin className="h-6 w-6 text-amber-600" />;
            default:
                return <Landmark className="text-thh-primary h-6 w-6" />;
        }
    };

    return (
        <AdminLayout title={`Manage ${meta.title}`}>
            <div className="space-y-6">
                {/* Top Nav & Breadcrumb */}
                <div className="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                    <div className="flex items-center gap-3">
                        <Link
                            href="/admin/content"
                            className="bg-thh-surface border-thh-border hover:bg-thh-bg text-thh-text rounded-xl border p-2 shadow-xs transition-colors"
                            title="Back to All Modules"
                        >
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                        <div>
                            <div className="flex items-center gap-2">
                                <h1 className="text-thh-text text-xl font-bold tracking-tight">
                                    {meta.title}
                                </h1>
                                <span className="bg-thh-primary/10 text-thh-primary rounded-lg px-2 py-0.5 text-xs font-semibold">
                                    {items.total} total
                                </span>
                            </div>
                            <p className="text-thh-text-muted text-xs">
                                {meta.title_gu} • {meta.description}
                            </p>
                        </div>
                    </div>

                    <div className="flex items-center gap-3">
                        {/* Search Bar */}
                        <form onSubmit={handleSearch} className="relative">
                            <input
                                type="text"
                                placeholder={`Search ${meta.title.toLowerCase()}...`}
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                className="bg-thh-surface border-thh-border text-thh-text placeholder:text-thh-text-muted focus:border-thh-primary w-52 rounded-xl border py-2 pr-4 pl-9 text-xs focus:outline-hidden sm:w-64"
                            />
                            <Search className="text-thh-text-muted absolute top-2.5 left-3 h-3.5 w-3.5" />
                        </form>

                        {/* Create Button */}
                        <button
                            type="button"
                            onClick={() => setIsCreateModalOpen(true)}
                            className="bg-thh-primary hover:bg-thh-primary/90 flex items-center gap-1.5 rounded-xl px-3.5 py-2 text-xs font-bold text-white shadow-sm transition-all"
                        >
                            <Plus className="h-4 w-4" />
                            <span>Add {meta.singular}</span>
                        </button>
                    </div>
                </div>

                {/* Data Table */}
                <div className="bg-thh-surface border-thh-border overflow-hidden rounded-2xl border shadow-xs">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-xs">
                            <thead className="bg-thh-bg/60 border-thh-border text-thh-text-muted border-b text-[11px] font-bold tracking-wider uppercase">
                                <tr>
                                    <th className="px-5 py-3.5">#</th>
                                    <th className="px-5 py-3.5">
                                        Title / Details
                                    </th>
                                    <th className="px-5 py-3.5">
                                        Key Attributes
                                    </th>
                                    <th className="px-5 py-3.5">Status</th>
                                    <th className="px-5 py-3.5 text-right">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-thh-border divide-y">
                                {items.data.length === 0 ? (
                                    <tr>
                                        <td
                                            colSpan={5}
                                            className="text-thh-text-muted py-12 text-center"
                                        >
                                            No records found for this module.
                                        </td>
                                    </tr>
                                ) : (
                                    items.data.map((item, idx) => {
                                        const isPublishedOrActive =
                                            item.is_published ??
                                            item.is_active ??
                                            (item.status === 'active' ||
                                                item.status === 'resolved');

                                        return (
                                            <tr
                                                key={item.id}
                                                className="hover:bg-thh-bg/40 transition-colors"
                                            >
                                                <td className="text-thh-text-muted px-5 py-3.5 font-mono">
                                                    {(items.current_page - 1) *
                                                        items.per_page +
                                                        idx +
                                                        1}
                                                </td>

                                                {/* Column 2: Title / Primary identifier */}
                                                <td className="px-5 py-3.5">
                                                    <div className="text-thh-text font-semibold">
                                                        {item.title ||
                                                            item.name ||
                                                            item.patient_name ||
                                                            item.benefits
                                                                ?.title ||
                                                            item.slug}
                                                    </div>
                                                    <div className="text-thh-text-muted mt-0.5 text-[11px]">
                                                        {item.company ||
                                                            item.organizer ||
                                                            item.leader_name ||
                                                            item.slug ||
                                                            item.category ||
                                                            `ID: #${item.id}`}
                                                    </div>
                                                </td>

                                                {/* Column 3: Contextual attributes */}
                                                <td className="text-thh-text-muted px-5 py-3.5">
                                                    {meta.slug ===
                                                        'schemes' && (
                                                        <span>
                                                            Docs:{' '}
                                                            <strong className="text-thh-text">
                                                                {item
                                                                    .required_documents
                                                                    ?.length ||
                                                                    0}{' '}
                                                                required
                                                            </strong>
                                                        </span>
                                                    )}
                                                    {meta.slug ===
                                                        'scholarships' && (
                                                        <span>
                                                            Grant:{' '}
                                                            <strong className="text-thh-primary">
                                                                ₹
                                                                {Number(
                                                                    item.amount,
                                                                ).toLocaleString(
                                                                    'en-IN',
                                                                )}
                                                            </strong>
                                                        </span>
                                                    )}
                                                    {meta.slug === 'jobs' && (
                                                        <span>
                                                            {item.location} •{' '}
                                                            <strong className="text-thh-text">
                                                                {
                                                                    item.salary_range
                                                                }
                                                            </strong>
                                                        </span>
                                                    )}
                                                    {meta.slug === 'health' && (
                                                        <span>
                                                            {item.district
                                                                ?.name ||
                                                                'District'}{' '}
                                                            •{' '}
                                                            <strong className="text-thh-text">
                                                                {item.scheduled_at
                                                                    ? new Date(
                                                                          item.scheduled_at,
                                                                      ).toLocaleDateString()
                                                                    : 'N/A'}
                                                            </strong>
                                                        </span>
                                                    )}
                                                    {meta.slug === 'blood' && (
                                                        <span>
                                                            Group:{' '}
                                                            <strong className="font-bold text-red-600">
                                                                {
                                                                    item.blood_group
                                                                }
                                                            </strong>{' '}
                                                            (
                                                            {
                                                                item.units_required
                                                            }{' '}
                                                            units) •{' '}
                                                            {item.contact_phone}
                                                        </span>
                                                    )}
                                                    {meta.slug ===
                                                        'mock_tests' && (
                                                        <span>
                                                            {
                                                                item.duration_minutes
                                                            }{' '}
                                                            mins •{' '}
                                                            <strong className="text-thh-text">
                                                                {
                                                                    item.total_marks
                                                                }{' '}
                                                                marks
                                                            </strong>{' '}
                                                            (
                                                            {item.questions_count ??
                                                                0}{' '}
                                                            questions)
                                                        </span>
                                                    )}
                                                    {meta.slug === 'sakhi' && (
                                                        <span>
                                                            Leader:{' '}
                                                            {item.leader_name} •{' '}
                                                            <strong className="text-thh-text">
                                                                {
                                                                    item.members_count
                                                                }{' '}
                                                                members
                                                            </strong>
                                                        </span>
                                                    )}
                                                    {meta.slug ===
                                                        'village_reports' && (
                                                        <span>
                                                            {item.village
                                                                ?.name ||
                                                                'Village'}{' '}
                                                            • Cat:{' '}
                                                            <strong className="text-thh-text uppercase">
                                                                {item.category}
                                                            </strong>
                                                        </span>
                                                    )}
                                                </td>

                                                {/* Column 4: Status Badge */}
                                                <td className="px-5 py-3.5">
                                                    {isPublishedOrActive ? (
                                                        <span className="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-2.5 py-0.5 text-[11px] font-bold text-emerald-700 dark:text-emerald-400">
                                                            <CheckCircle className="h-3 w-3" />
                                                            {item.status ||
                                                                'Active'}
                                                        </span>
                                                    ) : (
                                                        <span className="inline-flex items-center gap-1 rounded-full bg-stone-500/10 px-2.5 py-0.5 text-[11px] font-bold text-stone-600 dark:text-stone-400">
                                                            <XCircle className="h-3 w-3" />
                                                            Inactive
                                                        </span>
                                                    )}
                                                </td>

                                                {/* Column 5: Actions */}
                                                <td className="px-5 py-3.5 text-right">
                                                    <button
                                                        type="button"
                                                        onClick={() =>
                                                            handleToggle(
                                                                item.id,
                                                            )
                                                        }
                                                        className="bg-thh-bg border-thh-border hover:border-thh-primary text-thh-text rounded-lg border px-2.5 py-1 text-[11px] font-semibold transition-colors"
                                                    >
                                                        {meta.slug ===
                                                        'village_reports'
                                                            ? `Mark ${item.status === 'pending' ? 'Investigating' : item.status === 'investigating' ? 'Resolved' : 'Pending'}`
                                                            : isPublishedOrActive
                                                              ? 'Deactivate'
                                                              : 'Activate'}
                                                    </button>
                                                </td>
                                            </tr>
                                        );
                                    })
                                )}
                            </tbody>
                        </table>
                    </div>

                    {/* Pagination */}
                    {items.last_page > 1 && (
                        <div className="border-thh-border flex items-center justify-between border-t px-5 py-3">
                            <span className="text-thh-text-muted text-xs">
                                Page {items.current_page} of {items.last_page} (
                                {items.total} records)
                            </span>
                            <div className="flex items-center gap-1">
                                {items.links.map((link, i) => (
                                    <Link
                                        key={i}
                                        href={link.url || '#'}
                                        dangerouslySetInnerHTML={{
                                            __html: link.label,
                                        }}
                                        className={`rounded-lg px-2.5 py-1 text-xs font-semibold ${
                                            link.active
                                                ? 'bg-thh-primary text-white'
                                                : link.url
                                                  ? 'text-thh-text hover:bg-thh-bg'
                                                  : 'text-thh-text-muted pointer-events-none opacity-40'
                                        }`}
                                    />
                                ))}
                            </div>
                        </div>
                    )}
                </div>

                {/* Create Record Modal */}
                {isCreateModalOpen && (
                    <div className="animate-fade-in fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs">
                        <div className="bg-thh-surface border-thh-border max-h-[90vh] w-full max-w-lg space-y-4 overflow-y-auto rounded-2xl border p-6 shadow-2xl">
                            <div className="border-thh-border flex items-center justify-between border-b pb-3">
                                <div className="flex items-center gap-2">
                                    <div className="bg-thh-primary/10 rounded-lg p-1.5">
                                        {getModuleIcon(meta.slug)}
                                    </div>
                                    <h3 className="text-thh-text text-base font-bold">
                                        Add New {meta.singular}
                                    </h3>
                                </div>
                                <button
                                    type="button"
                                    onClick={() => setIsCreateModalOpen(false)}
                                    className="text-thh-text-muted hover:text-thh-text rounded-lg p-1"
                                >
                                    <X className="h-4 w-4" />
                                </button>
                            </div>

                            <form
                                onSubmit={submitCreate}
                                className="space-y-3.5 text-xs"
                            >
                                {/* Common Title / Name */}
                                <div>
                                    <label className="text-thh-text mb-1 block font-semibold">
                                        {meta.slug === 'sakhi'
                                            ? 'Circle Name'
                                            : meta.slug === 'blood'
                                              ? 'Patient Name'
                                              : 'Title / Name'}
                                    </label>
                                    <input
                                        type="text"
                                        required
                                        value={
                                            meta.slug === 'sakhi'
                                                ? data.name
                                                : meta.slug === 'blood'
                                                  ? data.patient_name
                                                  : data.title
                                        }
                                        onChange={(e) => {
                                            if (meta.slug === 'sakhi')
                                                setData('name', e.target.value);
                                            else if (meta.slug === 'blood')
                                                setData(
                                                    'patient_name',
                                                    e.target.value,
                                                );
                                            else
                                                setData(
                                                    'title',
                                                    e.target.value,
                                                );
                                        }}
                                        placeholder={`Enter ${meta.singular.toLowerCase()} name...`}
                                        className="bg-thh-bg border-thh-border text-thh-text focus:border-thh-primary w-full rounded-xl border p-2.5 focus:outline-hidden"
                                    />
                                </div>

                                {/* Schemes Specific */}
                                {meta.slug === 'schemes' && (
                                    <>
                                        <div>
                                            <label className="text-thh-text mb-1 block font-semibold">
                                                Benefit Summary
                                            </label>
                                            <textarea
                                                required
                                                rows={2}
                                                value={data.benefit_summary}
                                                onChange={(e) =>
                                                    setData(
                                                        'benefit_summary',
                                                        e.target.value,
                                                    )
                                                }
                                                placeholder="e.g. ₹1,20,000 grant for house construction in 3 installments"
                                                className="bg-thh-bg border-thh-border text-thh-text focus:border-thh-primary w-full rounded-xl border p-2.5 focus:outline-hidden"
                                            />
                                        </div>
                                        <div>
                                            <label className="text-thh-text mb-1 block font-semibold">
                                                Required Documents (comma
                                                separated)
                                            </label>
                                            <input
                                                type="text"
                                                value={data.documents}
                                                onChange={(e) =>
                                                    setData(
                                                        'documents',
                                                        e.target.value,
                                                    )
                                                }
                                                placeholder="Aadhaar Card, Caste Certificate, 7/12 Land Record"
                                                className="bg-thh-bg border-thh-border text-thh-text focus:border-thh-primary w-full rounded-xl border p-2.5 focus:outline-hidden"
                                            />
                                        </div>
                                    </>
                                )}

                                {/* Scholarships Specific */}
                                {meta.slug === 'scholarships' && (
                                    <>
                                        <div className="grid grid-cols-2 gap-3">
                                            <div>
                                                <label className="text-thh-text mb-1 block font-semibold">
                                                    Scholarship Amount (₹)
                                                </label>
                                                <input
                                                    type="number"
                                                    required
                                                    value={data.amount}
                                                    onChange={(e) =>
                                                        setData(
                                                            'amount',
                                                            e.target.value,
                                                        )
                                                    }
                                                    placeholder="15000"
                                                    className="bg-thh-bg border-thh-border text-thh-text focus:border-thh-primary w-full rounded-xl border p-2.5 focus:outline-hidden"
                                                />
                                            </div>
                                            <div>
                                                <label className="text-thh-text mb-1 block font-semibold">
                                                    Deadline (Days from now)
                                                </label>
                                                <input
                                                    type="number"
                                                    value={data.deadline_days}
                                                    onChange={(e) =>
                                                        setData(
                                                            'deadline_days',
                                                            e.target.value,
                                                        )
                                                    }
                                                    className="bg-thh-bg border-thh-border text-thh-text focus:border-thh-primary w-full rounded-xl border p-2.5 focus:outline-hidden"
                                                />
                                            </div>
                                        </div>
                                    </>
                                )}

                                {/* Jobs Specific */}
                                {meta.slug === 'jobs' && (
                                    <>
                                        <div className="grid grid-cols-2 gap-3">
                                            <div>
                                                <label className="text-thh-text mb-1 block font-semibold">
                                                    Employer / Company
                                                </label>
                                                <input
                                                    type="text"
                                                    required
                                                    value={data.company}
                                                    onChange={(e) =>
                                                        setData(
                                                            'company',
                                                            e.target.value,
                                                        )
                                                    }
                                                    placeholder="GGVT Field Mission"
                                                    className="bg-thh-bg border-thh-border text-thh-text focus:border-thh-primary w-full rounded-xl border p-2.5 focus:outline-hidden"
                                                />
                                            </div>
                                            <div>
                                                <label className="text-thh-text mb-1 block font-semibold">
                                                    Location
                                                </label>
                                                <input
                                                    type="text"
                                                    required
                                                    value={data.location}
                                                    onChange={(e) =>
                                                        setData(
                                                            'location',
                                                            e.target.value,
                                                        )
                                                    }
                                                    placeholder="Ahwa, Dang"
                                                    className="bg-thh-bg border-thh-border text-thh-text focus:border-thh-primary w-full rounded-xl border p-2.5 focus:outline-hidden"
                                                />
                                            </div>
                                        </div>
                                        <div>
                                            <label className="text-thh-text mb-1 block font-semibold">
                                                Salary / Stipend
                                            </label>
                                            <input
                                                type="text"
                                                value={data.salary_range}
                                                onChange={(e) =>
                                                    setData(
                                                        'salary_range',
                                                        e.target.value,
                                                    )
                                                }
                                                placeholder="₹18,000 - ₹25,000 / month"
                                                className="bg-thh-bg border-thh-border text-thh-text focus:border-thh-primary w-full rounded-xl border p-2.5 focus:outline-hidden"
                                            />
                                        </div>
                                    </>
                                )}

                                {/* Health Camps Specific */}
                                {meta.slug === 'health' && (
                                    <>
                                        <div className="grid grid-cols-2 gap-3">
                                            <div>
                                                <label className="text-thh-text mb-1 block font-semibold">
                                                    Organizer
                                                </label>
                                                <input
                                                    type="text"
                                                    value={data.organizer}
                                                    onChange={(e) =>
                                                        setData(
                                                            'organizer',
                                                            e.target.value,
                                                        )
                                                    }
                                                    placeholder="GGVT Mobile Health Unit"
                                                    className="bg-thh-bg border-thh-border text-thh-text focus:border-thh-primary w-full rounded-xl border p-2.5 focus:outline-hidden"
                                                />
                                            </div>
                                            <div>
                                                <label className="text-thh-text mb-1 block font-semibold">
                                                    District
                                                </label>
                                                <select
                                                    value={data.district_id}
                                                    onChange={(e) =>
                                                        setData(
                                                            'district_id',
                                                            e.target.value,
                                                        )
                                                    }
                                                    className="bg-thh-bg border-thh-border text-thh-text focus:border-thh-primary w-full rounded-xl border p-2.5 focus:outline-hidden"
                                                >
                                                    {districts.map((d) => (
                                                        <option
                                                            key={d.id}
                                                            value={d.id}
                                                        >
                                                            {d.name}
                                                        </option>
                                                    ))}
                                                </select>
                                            </div>
                                        </div>
                                        <div>
                                            <label className="text-thh-text mb-1 block font-semibold">
                                                Camp Address
                                            </label>
                                            <input
                                                type="text"
                                                required
                                                value={data.address}
                                                onChange={(e) =>
                                                    setData(
                                                        'address',
                                                        e.target.value,
                                                    )
                                                }
                                                placeholder="Panchayat Hall, Subir"
                                                className="bg-thh-bg border-thh-border text-thh-text focus:border-thh-primary w-full rounded-xl border p-2.5 focus:outline-hidden"
                                            />
                                        </div>
                                    </>
                                )}

                                {/* Blood Requests Specific */}
                                {meta.slug === 'blood' && (
                                    <>
                                        <div className="grid grid-cols-3 gap-3">
                                            <div>
                                                <label className="text-thh-text mb-1 block font-semibold">
                                                    Blood Group
                                                </label>
                                                <select
                                                    value={data.blood_group}
                                                    onChange={(e) =>
                                                        setData(
                                                            'blood_group',
                                                            e.target.value,
                                                        )
                                                    }
                                                    className="bg-thh-bg border-thh-border text-thh-text focus:border-thh-primary w-full rounded-xl border p-2.5 focus:outline-hidden"
                                                >
                                                    {[
                                                        'A+',
                                                        'A-',
                                                        'B+',
                                                        'B-',
                                                        'AB+',
                                                        'AB-',
                                                        'O+',
                                                        'O-',
                                                    ].map((bg) => (
                                                        <option
                                                            key={bg}
                                                            value={bg}
                                                        >
                                                            {bg}
                                                        </option>
                                                    ))}
                                                </select>
                                            </div>
                                            <div>
                                                <label className="text-thh-text mb-1 block font-semibold">
                                                    Units
                                                </label>
                                                <input
                                                    type="number"
                                                    min="1"
                                                    value={data.units_required}
                                                    onChange={(e) =>
                                                        setData(
                                                            'units_required',
                                                            e.target.value,
                                                        )
                                                    }
                                                    className="bg-thh-bg border-thh-border text-thh-text focus:border-thh-primary w-full rounded-xl border p-2.5 focus:outline-hidden"
                                                />
                                            </div>
                                            <div>
                                                <label className="text-thh-text mb-1 block font-semibold">
                                                    Phone
                                                </label>
                                                <input
                                                    type="text"
                                                    required
                                                    value={data.contact_phone}
                                                    onChange={(e) =>
                                                        setData(
                                                            'contact_phone',
                                                            e.target.value,
                                                        )
                                                    }
                                                    placeholder="9876543210"
                                                    className="bg-thh-bg border-thh-border text-thh-text focus:border-thh-primary w-full rounded-xl border p-2.5 focus:outline-hidden"
                                                />
                                            </div>
                                        </div>
                                    </>
                                )}

                                {/* Sakhi Specific */}
                                {meta.slug === 'sakhi' && (
                                    <div className="grid grid-cols-2 gap-3">
                                        <div>
                                            <label className="text-thh-text mb-1 block font-semibold">
                                                Leader Name
                                            </label>
                                            <input
                                                type="text"
                                                required
                                                value={data.leader_name}
                                                onChange={(e) =>
                                                    setData(
                                                        'leader_name',
                                                        e.target.value,
                                                    )
                                                }
                                                placeholder="Kavitaben Bhil"
                                                className="bg-thh-bg border-thh-border text-thh-text focus:border-thh-primary w-full rounded-xl border p-2.5 focus:outline-hidden"
                                            />
                                        </div>
                                        <div>
                                            <label className="text-thh-text mb-1 block font-semibold">
                                                Leader Phone
                                            </label>
                                            <input
                                                type="text"
                                                required
                                                value={data.leader_phone}
                                                onChange={(e) =>
                                                    setData(
                                                        'leader_phone',
                                                        e.target.value,
                                                    )
                                                }
                                                placeholder="9876500010"
                                                className="bg-thh-bg border-thh-border text-thh-text focus:border-thh-primary w-full rounded-xl border p-2.5 focus:outline-hidden"
                                            />
                                        </div>
                                    </div>
                                )}

                                {/* Village Reports Specific */}
                                {meta.slug === 'village_reports' && (
                                    <>
                                        <div className="grid grid-cols-2 gap-3">
                                            <div>
                                                <label className="text-thh-text mb-1 block font-semibold">
                                                    Category
                                                </label>
                                                <select
                                                    value={data.category}
                                                    onChange={(e) =>
                                                        setData(
                                                            'category',
                                                            e.target.value,
                                                        )
                                                    }
                                                    className="bg-thh-bg border-thh-border text-thh-text focus:border-thh-primary w-full rounded-xl border p-2.5 focus:outline-hidden"
                                                >
                                                    <option value="water">
                                                        Drinking Water
                                                    </option>
                                                    <option value="road">
                                                        Road & Bridge
                                                    </option>
                                                    <option value="electricity">
                                                        Electricity / Solar
                                                    </option>
                                                    <option value="school">
                                                        School Infrastructure
                                                    </option>
                                                    <option value="sanitation">
                                                        Sanitation / Health
                                                    </option>
                                                </select>
                                            </div>
                                            <div>
                                                <label className="text-thh-text mb-1 block font-semibold">
                                                    Village
                                                </label>
                                                <select
                                                    value={data.village_id}
                                                    onChange={(e) =>
                                                        setData(
                                                            'village_id',
                                                            e.target.value,
                                                        )
                                                    }
                                                    className="bg-thh-bg border-thh-border text-thh-text focus:border-thh-primary w-full rounded-xl border p-2.5 focus:outline-hidden"
                                                >
                                                    {villages.map((v) => (
                                                        <option
                                                            key={v.id}
                                                            value={v.id}
                                                        >
                                                            {v.name}
                                                        </option>
                                                    ))}
                                                </select>
                                            </div>
                                        </div>
                                        <div>
                                            <label className="text-thh-text mb-1 block font-semibold">
                                                Issue Description
                                            </label>
                                            <textarea
                                                required
                                                rows={3}
                                                value={data.description}
                                                onChange={(e) =>
                                                    setData(
                                                        'description',
                                                        e.target.value,
                                                    )
                                                }
                                                placeholder="Describe the issue reported by the villagers..."
                                                className="bg-thh-bg border-thh-border text-thh-text focus:border-thh-primary w-full rounded-xl border p-2.5 focus:outline-hidden"
                                            />
                                        </div>
                                    </>
                                )}

                                {/* Submit & Cancel Buttons */}
                                <div className="border-thh-border flex items-center justify-end gap-2 border-t pt-2">
                                    <button
                                        type="button"
                                        onClick={() =>
                                            setIsCreateModalOpen(false)
                                        }
                                        className="bg-thh-bg hover:bg-thh-border/50 text-thh-text rounded-xl px-4 py-2 font-semibold"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="bg-thh-primary hover:bg-thh-primary/90 rounded-xl px-4 py-2 font-bold text-white shadow-sm disabled:opacity-50"
                                    >
                                        {processing
                                            ? 'Saving...'
                                            : 'Save & Publish'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
