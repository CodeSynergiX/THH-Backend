import React, { useState } from 'react';
import { Link, router } from '@inertiajs/react';
import {
    FolderKanban,
    Table as TableIcon,
    Kanban,
    Search,
    Eye,
} from 'lucide-react';
import AdminLayout from '../../../components/AdminLayout';
import { useTranslation } from '../../../lib/i18n';

interface ApplicationItem {
    id: number;
    case_no: string;
    title: string;
    description: string;
    urgency: string;
    priority: string;
    status: string;
    created_at: string;
    sla_due_at?: string;
    user?: { id: number; name: string; phone?: string };
    category?: { id: number; slug: string };
    village?: { name_en: string; taluka?: { district?: { name_en: string } } };
    current_assignee?: { id: number; name: string };
}

interface PaginatedApplications {
    data: ApplicationItem[];
    current_page: number;
    last_page: number;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
}

interface CasesIndexProps {
    applications: PaginatedApplications | ApplicationItem[];
    view: 'table' | 'kanban';
    filters: {
        status: string;
        urgency: string;
        category_id: string;
        district_id: string;
        search: string;
    };
    status_counts: Record<string, number>;
    categories: Array<{ id: number; slug: string }>;
    districts: Array<{ id: number; name_en: string; name_gu: string }>;
}

export default function CasesIndex({
    applications,
    view: initialView,
    filters,
    status_counts,
    categories,
    districts,
}: CasesIndexProps) {
    const { t } = useTranslation();
    const [view, setView] = useState<'table' | 'kanban'>(
        initialView || 'table',
    );
    const [search, setSearch] = useState(filters.search || '');
    const [selectedStatus, setSelectedStatus] = useState(
        filters.status || 'all',
    );
    const [selectedUrgency, setSelectedUrgency] = useState(
        filters.urgency || 'all',
    );
    const [selectedCategory, setSelectedCategory] = useState(
        filters.category_id || 'all',
    );
    const [selectedDistrict, setSelectedDistrict] = useState(
        filters.district_id || 'all',
    );

    const handleApplyFilters = (
        newParams: Partial<typeof filters> & { view?: string },
    ) => {
        router.get(
            '/admin/cases',
            {
                view,
                status: selectedStatus,
                urgency: selectedUrgency,
                category_id: selectedCategory,
                district_id: selectedDistrict,
                search,
                ...newParams,
            },
            { preserveState: true, replace: true },
        );
    };

    const handleSearchSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        handleApplyFilters({ search });
    };

    const toggleView = (newView: 'table' | 'kanban') => {
        setView(newView);
        handleApplyFilters({ view: newView });
    };

    const kanbanColumns = [
        { key: 'received', label: 'Received' },
        { key: 'verification', label: 'Verification' },
        { key: 'categorised', label: 'Categorised' },
        { key: 'assigned', label: 'Assigned' },
        { key: 'assistance', label: 'Assistance' },
        { key: 'followUp', label: 'Follow-up' },
        { key: 'resolved', label: 'Resolved' },
    ];

    const allApps: ApplicationItem[] = Array.isArray(applications)
        ? applications
        : applications.data;

    return (
        <AdminLayout title="Applications & Cases Workspace">
            <div className="space-y-6">
                {/* Header and View Toggle */}
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 className="text-thh-text flex items-center gap-2.5 text-2xl font-bold tracking-tight">
                            <FolderKanban className="text-thh-primary h-6 w-6" />
                            <span>Case Lifecycle Workspace</span>
                        </h2>
                        <p className="text-thh-text-muted mt-1 text-xs">
                            End-to-end management from citizen submission to
                            field assistance and verified resolution.
                        </p>
                    </div>

                    <div className="flex items-center gap-2">
                        <div className="bg-thh-surface border-thh-border flex items-center rounded-xl border p-1">
                            <button
                                type="button"
                                onClick={() => toggleView('table')}
                                className={`flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition-all ${
                                    view === 'table'
                                        ? 'bg-thh-primary text-white shadow-2xs'
                                        : 'text-thh-text-muted hover:text-thh-text'
                                }`}
                            >
                                <TableIcon className="h-3.5 w-3.5" />
                                <span>Table</span>
                            </button>
                            <button
                                type="button"
                                onClick={() => toggleView('kanban')}
                                className={`flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition-all ${
                                    view === 'kanban'
                                        ? 'bg-thh-primary text-white shadow-2xs'
                                        : 'text-thh-text-muted hover:text-thh-text'
                                }`}
                            >
                                <Kanban className="h-3.5 w-3.5" />
                                <span>Kanban</span>
                            </button>
                        </div>
                    </div>
                </div>

                {/* Status Filter Badges */}
                <div className="flex items-center gap-2 overflow-x-auto pb-1">
                    {[
                        { key: 'all', label: 'All Cases' },
                        { key: 'received', label: 'Received' },
                        { key: 'verification', label: 'Verification' },
                        { key: 'categorised', label: 'Categorised' },
                        { key: 'assigned', label: 'Assigned' },
                        { key: 'assistance', label: 'Assistance' },
                        { key: 'followUp', label: 'Follow-up' },
                        { key: 'resolved', label: 'Resolved' },
                        { key: 'rejected', label: 'Rejected' },
                        { key: 'onHold', label: 'On Hold' },
                    ].map((st) => {
                        const count = status_counts[st.key] || 0;
                        const isSelected = selectedStatus === st.key;
                        return (
                            <button
                                key={st.key}
                                type="button"
                                onClick={() => {
                                    setSelectedStatus(st.key);
                                    handleApplyFilters({ status: st.key });
                                }}
                                className={`flex items-center gap-2 rounded-xl border px-3 py-1.5 text-xs font-semibold whitespace-nowrap transition-all ${
                                    isSelected
                                        ? 'bg-thh-surface border-thh-primary text-thh-primary ring-thh-primary shadow-2xs ring-1'
                                        : 'bg-thh-surface border-thh-border text-thh-text-muted hover:text-thh-text'
                                }`}
                            >
                                <span>{st.label}</span>
                                <span className="py-0.2 bg-thh-bg rounded-full px-1.5 font-mono text-[10px]">
                                    {count}
                                </span>
                            </button>
                        );
                    })}
                </div>

                {/* Filter and Search Bar */}
                <div className="bg-thh-surface border-thh-border flex flex-col items-center gap-4 rounded-2xl border p-4 shadow-2xs md:flex-row">
                    {/* Search */}
                    <form
                        onSubmit={handleSearchSubmit}
                        className="relative w-full flex-1"
                    >
                        <Search className="text-thh-text-muted absolute top-3 left-3.5 h-4 w-4" />
                        <input
                            type="text"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Search by Case No, Title, Citizen Name or Mobile..."
                            className="border-thh-border bg-thh-bg text-thh-text focus:ring-thh-primary w-full rounded-xl border py-2 pr-4 pl-10 text-xs focus:ring-1 focus:outline-hidden"
                        />
                    </form>

                    {/* Urgency Filter */}
                    <select
                        value={selectedUrgency}
                        onChange={(e) => {
                            setSelectedUrgency(e.target.value);
                            handleApplyFilters({ urgency: e.target.value });
                        }}
                        className="border-thh-border bg-thh-bg text-thh-text w-full rounded-xl border px-3 py-2 text-xs md:w-36"
                    >
                        <option value="all">All Urgencies</option>
                        <option value="critical">Critical</option>
                        <option value="urgent">Urgent</option>
                        <option value="normal">Normal</option>
                        <option value="low">Low</option>
                    </select>

                    {/* Category Filter */}
                    <select
                        value={selectedCategory}
                        onChange={(e) => {
                            setSelectedCategory(e.target.value);
                            handleApplyFilters({ category_id: e.target.value });
                        }}
                        className="border-thh-border bg-thh-bg text-thh-text w-full rounded-xl border px-3 py-2 text-xs md:w-40"
                    >
                        <option value="all">All Categories</option>
                        {categories.map((c) => (
                            <option key={c.id} value={c.id}>
                                {c.slug}
                            </option>
                        ))}
                    </select>

                    {/* District Filter */}
                    <select
                        value={selectedDistrict}
                        onChange={(e) => {
                            setSelectedDistrict(e.target.value);
                            handleApplyFilters({ district_id: e.target.value });
                        }}
                        className="border-thh-border bg-thh-bg text-thh-text w-full rounded-xl border px-3 py-2 text-xs md:w-40"
                    >
                        <option value="all">All Districts</option>
                        {districts.map((d) => (
                            <option key={d.id} value={d.id}>
                                {d.name_en}
                            </option>
                        ))}
                    </select>
                </div>

                {/* Table View */}
                {view === 'table' && (
                    <div className="bg-thh-surface border-thh-border overflow-hidden rounded-2xl border shadow-2xs">
                        <div className="overflow-x-auto">
                            <table className="w-full text-left text-xs">
                                <thead className="bg-thh-bg border-thh-border text-thh-text border-b text-[11px] font-bold tracking-wider uppercase">
                                    <tr>
                                        <th className="w-36 px-4 py-3.5">
                                            Case No
                                        </th>
                                        <th className="px-4 py-3.5">
                                            Title & Citizen
                                        </th>
                                        <th className="w-28 px-4 py-3.5">
                                            Category
                                        </th>
                                        <th className="w-36 px-4 py-3.5">
                                            Location
                                        </th>
                                        <th className="w-28 px-4 py-3.5">
                                            Status
                                        </th>
                                        <th className="w-24 px-4 py-3.5">
                                            Urgency
                                        </th>
                                        <th className="w-36 px-4 py-3.5">
                                            Assignee
                                        </th>
                                        <th className="w-24 px-4 py-3.5 text-right">
                                            Action
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-thh-border divide-y">
                                    {allApps.length === 0 ? (
                                        <tr>
                                            <td
                                                colSpan={8}
                                                className="text-thh-text-muted py-12 text-center text-sm"
                                            >
                                                {t(
                                                    'app.portal.empty_records',
                                                    'No case applications found matching filters.',
                                                )}
                                            </td>
                                        </tr>
                                    ) : (
                                        allApps.map((c) => (
                                            <tr
                                                key={c.id}
                                                className="hover:bg-thh-bg/40 transition-colors"
                                            >
                                                <td className="text-thh-primary px-4 py-3.5 font-mono font-bold">
                                                    <Link
                                                        href={`/admin/cases/${c.id}`}
                                                        className="hover:underline"
                                                    >
                                                        {c.case_no}
                                                    </Link>
                                                </td>
                                                <td className="px-4 py-3.5">
                                                    <span className="text-thh-text block font-bold">
                                                        {c.title}
                                                    </span>
                                                    <span className="text-thh-text-muted text-[11px]">
                                                        {c.user?.name || '-'}{' '}
                                                        {c.user?.phone
                                                            ? `(${c.user.phone})`
                                                            : ''}
                                                    </span>
                                                </td>
                                                <td className="text-thh-text px-4 py-3.5 font-semibold capitalize">
                                                    {c.category?.slug || '-'}
                                                </td>
                                                <td className="text-thh-text-muted px-4 py-3.5">
                                                    {c.village?.name_en ||
                                                        c.village?.taluka
                                                            ?.district
                                                            ?.name_en ||
                                                        '-'}
                                                </td>
                                                <td className="px-4 py-3.5">
                                                    <span className="bg-thh-bg text-thh-text border-thh-border rounded-full border px-2 py-0.5 text-[10px] font-bold uppercase">
                                                        ● {c.status}
                                                    </span>
                                                </td>
                                                <td className="px-4 py-3.5">
                                                    <span
                                                        className={`rounded-md px-2 py-0.5 text-[10px] font-bold uppercase ${
                                                            c.urgency ===
                                                            'critical'
                                                                ? 'bg-rose-500/10 text-rose-600'
                                                                : c.urgency ===
                                                                    'urgent'
                                                                  ? 'bg-amber-500/10 text-amber-600'
                                                                  : 'bg-thh-bg text-thh-text-muted'
                                                        }`}
                                                    >
                                                        {c.urgency}
                                                    </span>
                                                </td>
                                                <td className="text-thh-text px-4 py-3.5">
                                                    {c.current_assignee?.name ||
                                                        'Unassigned'}
                                                </td>
                                                <td className="px-4 py-3.5 text-right">
                                                    <Link
                                                        href={`/admin/cases/${c.id}`}
                                                        className="border-thh-border text-thh-text hover:bg-thh-bg inline-flex items-center gap-1 rounded-lg border px-2.5 py-1 text-xs font-semibold"
                                                    >
                                                        <Eye className="h-3 w-3" />
                                                        <span>View</span>
                                                    </Link>
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>
                )}

                {/* Kanban View */}
                {view === 'kanban' && (
                    <div className="grid grid-cols-1 gap-4 overflow-x-auto pb-4 md:grid-cols-3 lg:grid-cols-7">
                        {kanbanColumns.map((col) => {
                            const colCases = allApps.filter(
                                (c) =>
                                    c.status.toLowerCase() ===
                                    col.key.toLowerCase(),
                            );
                            return (
                                <div
                                    key={col.key}
                                    className="bg-thh-surface border-thh-border flex min-w-[240px] flex-col gap-3 rounded-2xl border p-3"
                                >
                                    <div className="border-thh-border flex items-center justify-between border-b pb-2">
                                        <span className="text-thh-text text-xs font-bold tracking-wider uppercase">
                                            {col.label}
                                        </span>
                                        <span className="bg-thh-bg border-thh-border rounded-full border px-2 py-0.5 font-mono text-[10px] font-bold">
                                            {colCases.length}
                                        </span>
                                    </div>

                                    <div className="max-h-[600px] flex-1 space-y-2.5 overflow-y-auto pr-0.5">
                                        {colCases.length === 0 ? (
                                            <div className="text-thh-text-muted border-thh-border rounded-xl border border-dashed py-8 text-center text-[11px]">
                                                No cases
                                            </div>
                                        ) : (
                                            colCases.map((c) => (
                                                <Link
                                                    key={c.id}
                                                    href={`/admin/cases/${c.id}`}
                                                    className="bg-thh-bg border-thh-border hover:border-thh-primary block space-y-2 rounded-xl border p-3 text-xs transition-all hover:shadow-2xs"
                                                >
                                                    <div className="flex items-center justify-between">
                                                        <span className="text-thh-primary font-mono text-[11px] font-bold">
                                                            {c.case_no}
                                                        </span>
                                                        <span className="py-0.2 bg-thh-surface text-thh-text-muted rounded-md px-1.5 text-[9px] font-bold uppercase">
                                                            {c.urgency}
                                                        </span>
                                                    </div>
                                                    <h5 className="text-thh-text line-clamp-2 leading-snug font-bold">
                                                        {c.title}
                                                    </h5>
                                                    <div className="text-thh-text-muted border-thh-border/50 flex items-center justify-between border-t pt-1 text-[10px]">
                                                        <span>
                                                            {c.user?.name ||
                                                                '-'}
                                                        </span>
                                                        <span>
                                                            {c.category?.slug ||
                                                                '-'}
                                                        </span>
                                                    </div>
                                                </Link>
                                            ))
                                        )}
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
