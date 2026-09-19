import React, { useState } from 'react';
import { router } from '@inertiajs/react';
import { Eye, Search, ShieldCheck } from 'lucide-react';
import AdminLayout from '../../../components/AdminLayout';

interface AuditItem {
    id: number;
    action: string;
    subject_type?: string;
    subject_id?: number;
    before?: Record<string, unknown>;
    after?: Record<string, unknown>;
    ip_address?: string;
    created_at: string;
    actor?: { id: number; name: string; email?: string };
}

interface PaginatedAudit {
    data: AuditItem[];
    current_page: number;
    last_page: number;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
}

interface AuditIndexProps {
    logs: PaginatedAudit;
    search: string;
    selectedAction: string;
    actions: string[];
}

export default function AuditIndex({
    logs,
    search: initialSearch,
    selectedAction,
    actions,
}: AuditIndexProps) {
    const [search, setSearch] = useState(initialSearch || '');
    const [selectedLog, setSelectedLog] = useState<AuditItem | null>(null);

    const handleFilter = (action: string, q: string) => {
        router.get(
            '/admin/audit',
            { action, search: q },
            { preserveState: true, replace: true },
        );
    };

    const handleSearchSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        handleFilter(selectedAction, search);
    };

    return (
        <AdminLayout title="System Audit Trail">
            <div className="space-y-6">
                {/* Header */}
                <div>
                    <h2 className="text-thh-text flex items-center gap-2.5 text-2xl font-bold tracking-tight">
                        <ShieldCheck className="text-thh-primary h-6 w-6" />
                        <span>System Audit Trail</span>
                    </h2>
                    <p className="text-thh-text-muted mt-1 text-xs">
                        Immutable, append-only security logs capturing every
                        case transition, reassignment, and configuration update.
                    </p>
                </div>

                {/* Filter & Search Bar */}
                <div className="bg-thh-surface border-thh-border flex flex-col items-center gap-4 rounded-2xl border p-4 shadow-2xs sm:flex-row">
                    <form
                        onSubmit={handleSearchSubmit}
                        className="relative w-full flex-1"
                    >
                        <Search className="text-thh-text-muted absolute top-3 left-3.5 h-4 w-4" />
                        <input
                            type="text"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Search by Action, IP Address, or Staff name..."
                            className="border-thh-border bg-thh-bg text-thh-text focus:ring-thh-primary w-full rounded-xl border py-2 pr-4 pl-10 text-xs focus:ring-1 focus:outline-hidden"
                        />
                    </form>

                    <select
                        value={selectedAction}
                        onChange={(e) => handleFilter(e.target.value, search)}
                        className="border-thh-border bg-thh-bg text-thh-text w-full rounded-xl border px-3 py-2 text-xs sm:w-60"
                    >
                        <option value="all">All Actions</option>
                        {actions.map((act) => (
                            <option key={act} value={act}>
                                {act}
                            </option>
                        ))}
                    </select>
                </div>

                {/* Logs Table */}
                <div className="bg-thh-surface border-thh-border overflow-hidden rounded-2xl border shadow-2xs">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-xs">
                            <thead className="bg-thh-bg border-thh-border text-thh-text border-b text-[11px] font-bold tracking-wider uppercase">
                                <tr>
                                    <th className="w-44 px-4 py-3.5">
                                        Timestamp
                                    </th>
                                    <th className="w-52 px-4 py-3.5">Action</th>
                                    <th className="px-4 py-3.5">Subject</th>
                                    <th className="w-40 px-4 py-3.5">Actor</th>
                                    <th className="w-32 px-4 py-3.5">
                                        IP Address
                                    </th>
                                    <th className="w-20 px-4 py-3.5 text-right">
                                        Details
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-thh-border divide-y">
                                {logs.data.length === 0 ? (
                                    <tr>
                                        <td
                                            colSpan={6}
                                            className="text-thh-text-muted py-12 text-center text-sm"
                                        >
                                            No audit logs found.
                                        </td>
                                    </tr>
                                ) : (
                                    logs.data.map((log) => (
                                        <tr
                                            key={log.id}
                                            className="hover:bg-thh-bg/40 transition-colors"
                                        >
                                            <td className="text-thh-text-muted px-4 py-3.5 font-mono text-[11px]">
                                                {new Date(
                                                    log.created_at,
                                                ).toLocaleString()}
                                            </td>
                                            <td className="px-4 py-3.5">
                                                <span className="text-thh-accent font-mono text-xs font-bold">
                                                    {log.action}
                                                </span>
                                            </td>
                                            <td className="text-thh-text px-4 py-3.5 font-mono text-[11px]">
                                                {log.subject_type
                                                    ? `${log.subject_type.split('\\').pop()} #${log.subject_id}`
                                                    : '—'}
                                            </td>
                                            <td className="text-thh-text px-4 py-3.5 font-semibold">
                                                {log.actor?.name || 'System'}
                                            </td>
                                            <td className="text-thh-text-muted px-4 py-3.5 font-mono text-[11px]">
                                                {log.ip_address || '127.0.0.1'}
                                            </td>
                                            <td className="px-4 py-3.5 text-right">
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        setSelectedLog(log)
                                                    }
                                                    className="border-thh-border text-thh-text hover:bg-thh-bg inline-flex items-center gap-1 rounded-lg border px-2 py-1 text-xs font-semibold"
                                                >
                                                    <Eye className="h-3 w-3" />
                                                    <span>Diff</span>
                                                </button>
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {/* Diff Modal */}
            {selectedLog && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs">
                    <div className="bg-thh-surface border-thh-border w-full max-w-2xl space-y-4 rounded-2xl border p-6 shadow-xl">
                        <div className="border-thh-border flex items-center justify-between border-b pb-3">
                            <div>
                                <h3 className="text-thh-primary font-mono text-sm font-bold">
                                    {selectedLog.action}
                                </h3>
                                <span className="text-thh-text-muted text-[11px]">
                                    By {selectedLog.actor?.name || 'System'} •{' '}
                                    {new Date(
                                        selectedLog.created_at,
                                    ).toLocaleString()}
                                </span>
                            </div>
                            <button
                                type="button"
                                onClick={() => setSelectedLog(null)}
                                className="text-thh-text-muted hover:text-thh-text text-xs font-semibold"
                            >
                                Close
                            </button>
                        </div>

                        <div className="grid grid-cols-1 gap-4 text-xs sm:grid-cols-2">
                            <div>
                                <span className="text-thh-text-muted mb-1 block text-[11px] font-bold uppercase">
                                    Before State
                                </span>
                                <pre className="bg-thh-bg border-thh-border text-thh-text max-h-60 overflow-x-auto rounded-xl border p-3 font-mono text-[11px]">
                                    {selectedLog.before
                                        ? JSON.stringify(
                                              selectedLog.before,
                                              null,
                                              2,
                                          )
                                        : 'null'}
                                </pre>
                            </div>
                            <div>
                                <span className="text-thh-text-muted mb-1 block text-[11px] font-bold uppercase">
                                    After State
                                </span>
                                <pre className="bg-thh-bg border-thh-border text-thh-text max-h-60 overflow-x-auto rounded-xl border p-3 font-mono text-[11px]">
                                    {selectedLog.after
                                        ? JSON.stringify(
                                              selectedLog.after,
                                              null,
                                              2,
                                          )
                                        : 'null'}
                                </pre>
                            </div>
                        </div>

                        <div className="flex justify-end pt-2">
                            <button
                                type="button"
                                onClick={() => setSelectedLog(null)}
                                className="bg-thh-primary rounded-lg px-4 py-2 text-xs font-semibold text-white"
                            >
                                Done
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </AdminLayout>
    );
}
