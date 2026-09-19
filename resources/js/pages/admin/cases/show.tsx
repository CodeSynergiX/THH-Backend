import React, { useState } from 'react';
import { Link, useForm } from '@inertiajs/react';
import {
    ArrowLeft,
    FileText,
    History,
    Lock,
    MessageSquare,
    Shield,
    User,
} from 'lucide-react';
import AdminLayout from '../../../components/AdminLayout';

interface CaseDetailProps {
    application: {
        id: number;
        case_no: string;
        title: string;
        description: string;
        urgency: string;
        priority: string;
        status: string;
        sla_due_at?: string;
        resolved_at?: string;
        rating?: number;
        feedback?: string;
        created_at: string;
        user?: {
            id: number;
            name: string;
            phone?: string;
            email?: string;
            age?: number;
            gender?: string;
            occupation?: string;
            education?: string;
            income_category?: string;
            community?: string;
            district?: { name_en: string };
            taluka?: { name_en: string };
            village?: { name_en: string };
        };
        category?: { id: number; slug: string };
        sub_category?: { id: number; slug: string };
        village?: {
            name_en: string;
            taluka?: { district?: { name_en: string } };
        };
        current_assignee?: { id: number; name: string; email?: string };
        documents?: Array<{
            id: number;
            type: string;
            path: string;
            status: string;
            created_at: string;
        }>;
        timeline_events?: Array<{
            id: number;
            event_type: string;
            from_status?: string;
            to_status?: string;
            title_key?: string;
            body?: string;
            actor_role?: string;
            visibility: string;
            created_at: string;
            actor?: { id: number; name: string };
        }>;
        messages?: Array<{
            id: number;
            body: string;
            created_at: string;
            sender?: { id: number; name: string };
        }>;
        follow_ups?: Array<{
            id: number;
            scheduled_at: string;
            notes?: string;
            status: string;
        }>;
    };
    auditLogs: Array<{
        id: number;
        action: string;
        before?: Record<string, unknown>;
        after?: Record<string, unknown>;
        created_at: string;
        actor?: { id: number; name: string };
    }>;
    availableStaff: Array<{
        id: number;
        name: string;
        assigned_applications_count?: number;
    }>;
    allowedTransitions: Array<{
        id: number;
        to_status: string;
        requires_note: boolean;
    }>;
}

export default function CaseShow({
    application,
    auditLogs,
    availableStaff,
    allowedTransitions,
}: CaseDetailProps) {
    const [activeTab, setActiveTab] = useState<
        'timeline' | 'documents' | 'messages' | 'followups' | 'audit'
    >('timeline');

    // Transition Form
    const [showTransitionModal, setShowTransitionModal] = useState(false);
    const {
        data: transData,
        setData: setTransData,
        post: postTrans,
        processing: transProcessing,
    } = useForm({
        to_status: allowedTransitions[0]?.to_status || '',
        note: '',
        visibility: 'public',
    });

    // Internal Note Form
    const [showNoteModal, setShowNoteModal] = useState(false);
    const {
        data: noteData,
        setData: setNoteData,
        post: postNote,
        processing: noteProcessing,
        reset: resetNote,
    } = useForm({
        note: '',
    });

    // Assign Form
    const [showAssignModal, setShowAssignModal] = useState(false);
    const {
        data: assignData,
        setData: setAssignData,
        post: postAssign,
        processing: assignProcessing,
    } = useForm({
        assignee_id: availableStaff[0]?.id || '',
        reason: '',
    });

    const handleTransitionSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        postTrans(`/admin/cases/${application.id}/transition`, {
            onSuccess: () => setShowTransitionModal(false),
        });
    };

    const handleNoteSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        postNote(`/admin/cases/${application.id}/note`, {
            onSuccess: () => {
                setShowNoteModal(false);
                resetNote();
            },
        });
    };

    const handleAssignSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        postAssign(`/admin/cases/${application.id}/assign`, {
            onSuccess: () => setShowAssignModal(false),
        });
    };

    const isSlaBreached =
        application.sla_due_at &&
        new Date(application.sla_due_at).getTime() < Date.now() &&
        application.status !== 'resolved' &&
        application.status !== 'rejected';

    return (
        <AdminLayout title={`Case ${application.case_no}`}>
            <div className="space-y-6">
                {/* Back Link and Header */}
                <div className="flex items-center gap-3">
                    <Link
                        href="/admin/cases"
                        className="border-thh-border bg-thh-surface text-thh-text hover:bg-thh-bg rounded-xl border p-2 transition-colors"
                    >
                        <ArrowLeft className="h-4 w-4" />
                    </Link>
                    <div>
                        <div className="flex items-center gap-3">
                            <h2 className="text-thh-primary font-mono text-xl font-black">
                                {application.case_no}
                            </h2>
                            <span className="bg-thh-surface text-thh-text border-thh-border rounded-full border px-3 py-1 text-xs font-bold tracking-wider uppercase">
                                ● {application.status}
                            </span>
                            <span
                                className={`rounded-md px-2.5 py-0.5 text-[10px] font-bold uppercase ${
                                    application.urgency === 'critical'
                                        ? 'bg-rose-500/10 text-rose-600'
                                        : 'bg-amber-500/10 text-amber-600'
                                }`}
                            >
                                {application.urgency}
                            </span>
                        </div>
                        <h3 className="text-thh-text mt-1 text-lg font-bold">
                            {application.title}
                        </h3>
                    </div>
                </div>

                {/* Top Action Bar */}
                <div className="bg-thh-surface border-thh-border flex flex-wrap items-center justify-between gap-3 rounded-2xl border p-4 shadow-2xs">
                    <div className="flex items-center gap-4 text-xs">
                        <div>
                            <span className="text-thh-text-muted block text-[10px] font-semibold uppercase">
                                Current Assignee
                            </span>
                            <span className="text-thh-text font-bold">
                                {application.current_assignee?.name ||
                                    'Unassigned'}
                            </span>
                        </div>
                        <div className="border-thh-border border-l pl-4">
                            <span className="text-thh-text-muted block text-[10px] font-semibold uppercase">
                                SLA Deadline
                            </span>
                            <span
                                className={`font-bold ${isSlaBreached ? 'text-rose-600' : 'text-thh-text'}`}
                            >
                                {application.sla_due_at
                                    ? new Date(
                                          application.sla_due_at,
                                      ).toLocaleDateString()
                                    : '-'}
                            </span>
                        </div>
                    </div>

                    <div className="flex items-center gap-2">
                        {allowedTransitions.length > 0 && (
                            <button
                                type="button"
                                onClick={() => setShowTransitionModal(true)}
                                className="bg-thh-primary rounded-xl px-3.5 py-2 text-xs font-bold text-white shadow-xs hover:opacity-95"
                            >
                                Advance Status
                            </button>
                        )}

                        <button
                            type="button"
                            onClick={() => setShowNoteModal(true)}
                            className="border-thh-border bg-thh-surface text-thh-text hover:bg-thh-bg rounded-xl border px-3.5 py-2 text-xs font-semibold shadow-2xs"
                        >
                            + Internal Note
                        </button>

                        <button
                            type="button"
                            onClick={() => setShowAssignModal(true)}
                            className="border-thh-border bg-thh-surface text-thh-text hover:bg-thh-bg rounded-xl border px-3.5 py-2 text-xs font-semibold shadow-2xs"
                        >
                            Reassign
                        </button>
                    </div>
                </div>

                {/* 2-Column Layout: Citizen Demographics (4 Cols) & Tabbed Workspace (8 Cols) */}
                <div className="grid grid-cols-1 gap-6 lg:grid-cols-12">
                    {/* Left: Citizen Profile Card */}
                    <div className="space-y-6 lg:col-span-4">
                        <div className="bg-thh-surface border-thh-border space-y-4 rounded-2xl border p-5 shadow-2xs">
                            <div className="border-thh-border flex items-center gap-2 border-b pb-3">
                                <User className="text-thh-primary h-4 w-4" />
                                <h4 className="text-thh-text text-xs font-bold tracking-wider uppercase">
                                    Citizen Demographics
                                </h4>
                            </div>

                            <div className="space-y-3 text-xs">
                                <div>
                                    <span className="text-thh-text-muted block text-[10px]">
                                        Name
                                    </span>
                                    <span className="text-thh-text text-sm font-bold">
                                        {application.user?.name || '-'}
                                    </span>
                                </div>
                                <div className="grid grid-cols-2 gap-2">
                                    <div>
                                        <span className="text-thh-text-muted block text-[10px]">
                                            Phone
                                        </span>
                                        <span className="text-thh-text font-mono">
                                            {application.user?.phone || '-'}
                                        </span>
                                    </div>
                                    <div>
                                        <span className="text-thh-text-muted block text-[10px]">
                                            Age / Gender
                                        </span>
                                        <span className="text-thh-text">
                                            {application.user?.age || '-'} •{' '}
                                            {application.user?.gender || '-'}
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <span className="text-thh-text-muted block text-[10px]">
                                        Village & District
                                    </span>
                                    <span className="text-thh-text font-medium">
                                        {application.village?.name_en || '-'},{' '}
                                        {application.village?.taluka?.district
                                            ?.name_en || '-'}
                                    </span>
                                </div>
                                <div className="grid grid-cols-2 gap-2">
                                    <div>
                                        <span className="text-thh-text-muted block text-[10px]">
                                            Education
                                        </span>
                                        <span className="text-thh-text">
                                            {application.user?.education || '-'}
                                        </span>
                                    </div>
                                    <div>
                                        <span className="text-thh-text-muted block text-[10px]">
                                            Income
                                        </span>
                                        <span className="text-thh-text">
                                            {application.user
                                                ?.income_category || '-'}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Description Card */}
                        <div className="bg-thh-surface border-thh-border space-y-3 rounded-2xl border p-5 shadow-2xs">
                            <h4 className="text-thh-text text-xs font-bold tracking-wider uppercase">
                                Problem Description
                            </h4>
                            <p className="text-thh-text text-xs leading-relaxed whitespace-pre-line">
                                {application.description}
                            </p>
                        </div>
                    </div>

                    {/* Right: Tabbed Workspace */}
                    <div className="space-y-6 lg:col-span-8">
                        {/* Tab Bar */}
                        <div className="bg-thh-surface border-thh-border flex items-center gap-1.5 overflow-x-auto rounded-xl border p-1">
                            {[
                                {
                                    key: 'timeline',
                                    label: 'Timeline & History',
                                    icon: History,
                                },
                                {
                                    key: 'documents',
                                    label: 'Documents',
                                    icon: FileText,
                                },
                                {
                                    key: 'messages',
                                    label: 'Messages',
                                    icon: MessageSquare,
                                },
                                {
                                    key: 'audit',
                                    label: 'Audit Trail',
                                    icon: Shield,
                                },
                            ].map((tb) => {
                                const Icon = tb.icon;
                                const isAct = activeTab === tb.key;
                                return (
                                    <button
                                        key={tb.key}
                                        type="button"
                                        onClick={() =>
                                            setActiveTab(tb.key as any)
                                        }
                                        className={`flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-bold whitespace-nowrap transition-all ${
                                            isAct
                                                ? 'bg-thh-primary text-white shadow-2xs'
                                                : 'text-thh-text-muted hover:text-thh-text hover:bg-thh-bg'
                                        }`}
                                    >
                                        <Icon className="h-3.5 w-3.5" />
                                        <span>{tb.label}</span>
                                    </button>
                                );
                            })}
                        </div>

                        {/* Timeline Tab */}
                        {activeTab === 'timeline' && (
                            <div className="bg-thh-surface border-thh-border space-y-4 rounded-2xl border p-6 shadow-2xs">
                                <h4 className="text-thh-text border-thh-border border-b pb-3 text-xs font-bold tracking-wider uppercase">
                                    Append-Only Timeline
                                </h4>

                                <div className="before:bg-thh-border relative space-y-6 pl-6 before:absolute before:top-3 before:bottom-3 before:left-2.5 before:w-0.5">
                                    {(application.timeline_events || []).map(
                                        (ev) => {
                                            const isInternal =
                                                ev.visibility === 'internal';
                                            return (
                                                <div
                                                    key={ev.id}
                                                    className="relative space-y-1"
                                                >
                                                    <div
                                                        className={`border-thh-surface absolute top-1 -left-6 h-3 w-3 rounded-full border-2 ${
                                                            isInternal
                                                                ? 'bg-amber-500'
                                                                : 'bg-thh-primary'
                                                        }`}
                                                    />
                                                    <div className="flex items-center justify-between text-xs">
                                                        <div className="flex items-center gap-2">
                                                            <span className="text-thh-text font-bold">
                                                                {ev.actor
                                                                    ?.name ||
                                                                    'System'}{' '}
                                                                (
                                                                {ev.actor_role ||
                                                                    'Staff'}
                                                                )
                                                            </span>
                                                            {isInternal && (
                                                                <span className="py-0.2 rounded-full border border-amber-500/30 bg-amber-500/10 px-2 text-[10px] font-bold text-amber-600">
                                                                    Internal
                                                                    Only
                                                                </span>
                                                            )}
                                                        </div>
                                                        <span className="text-thh-text-muted text-[11px]">
                                                            {new Date(
                                                                ev.created_at,
                                                            ).toLocaleString()}
                                                        </span>
                                                    </div>
                                                    {ev.body && (
                                                        <p className="text-thh-text bg-thh-bg border-thh-border mt-1 rounded-xl border p-3 text-xs">
                                                            {ev.body}
                                                        </p>
                                                    )}
                                                </div>
                                            );
                                        },
                                    )}
                                </div>
                            </div>
                        )}

                        {/* Documents Tab */}
                        {activeTab === 'documents' && (
                            <div className="bg-thh-surface border-thh-border space-y-4 rounded-2xl border p-6 shadow-2xs">
                                <h4 className="text-thh-text border-thh-border border-b pb-3 text-xs font-bold tracking-wider uppercase">
                                    Attached Documents (
                                    {application.documents?.length || 0})
                                </h4>

                                {(application.documents || []).length === 0 ? (
                                    <p className="text-thh-text-muted py-8 text-center text-xs">
                                        No documents attached.
                                    </p>
                                ) : (
                                    <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        {application.documents?.map((doc) => (
                                            <div
                                                key={doc.id}
                                                className="bg-thh-bg border-thh-border flex items-center justify-between rounded-xl border p-3 text-xs"
                                            >
                                                <div className="flex items-center gap-2.5">
                                                    <FileText className="text-thh-primary h-5 w-5" />
                                                    <div>
                                                        <span className="text-thh-text block font-bold">
                                                            {doc.type}
                                                        </span>
                                                        <span className="text-thh-text-muted text-[10px]">
                                                            {doc.status}
                                                        </span>
                                                    </div>
                                                </div>
                                                <a
                                                    href={`/storage/${doc.path}`}
                                                    target="_blank"
                                                    rel="noreferrer"
                                                    className="text-thh-primary px-2.5 py-1 text-[11px] font-bold hover:underline"
                                                >
                                                    View
                                                </a>
                                            </div>
                                        ))}
                                    </div>
                                )}
                            </div>
                        )}

                        {/* Messages Tab */}
                        {activeTab === 'messages' && (
                            <div className="bg-thh-surface border-thh-border space-y-4 rounded-2xl border p-6 shadow-2xs">
                                <h4 className="text-thh-text border-thh-border border-b pb-3 text-xs font-bold tracking-wider uppercase">
                                    Direct Case Communications
                                </h4>

                                {(application.messages || []).length === 0 ? (
                                    <p className="text-thh-text-muted py-8 text-center text-xs">
                                        No communication messages yet.
                                    </p>
                                ) : (
                                    <div className="space-y-3">
                                        {application.messages?.map((msg) => (
                                            <div
                                                key={msg.id}
                                                className="bg-thh-bg border-thh-border space-y-1 rounded-xl border p-3 text-xs"
                                            >
                                                <div className="flex items-center justify-between">
                                                    <span className="text-thh-text font-bold">
                                                        {msg.sender?.name ||
                                                            'Staff'}
                                                    </span>
                                                    <span className="text-thh-text-muted text-[10px]">
                                                        {new Date(
                                                            msg.created_at,
                                                        ).toLocaleString()}
                                                    </span>
                                                </div>
                                                <p className="text-thh-text">
                                                    {msg.body}
                                                </p>
                                            </div>
                                        ))}
                                    </div>
                                )}
                            </div>
                        )}

                        {/* Audit Trail Tab */}
                        {activeTab === 'audit' && (
                            <div className="bg-thh-surface border-thh-border space-y-4 rounded-2xl border p-6 shadow-2xs">
                                <h4 className="text-thh-text border-thh-border border-b pb-3 text-xs font-bold tracking-wider uppercase">
                                    Audit Trail State Diffs
                                </h4>

                                {auditLogs.length === 0 ? (
                                    <p className="text-thh-text-muted py-8 text-center text-xs">
                                        No audit entries.
                                    </p>
                                ) : (
                                    <div className="space-y-3">
                                        {auditLogs.map((log) => (
                                            <div
                                                key={log.id}
                                                className="bg-thh-bg border-thh-border space-y-2 rounded-xl border p-3.5 text-xs"
                                            >
                                                <div className="flex items-center justify-between">
                                                    <span className="text-thh-accent font-mono font-bold">
                                                        {log.action}
                                                    </span>
                                                    <span className="text-thh-text-muted text-[10px]">
                                                        {new Date(
                                                            log.created_at,
                                                        ).toLocaleString()}
                                                    </span>
                                                </div>
                                                <span className="text-thh-text block text-[11px]">
                                                    by{' '}
                                                    {log.actor?.name ||
                                                        'System'}
                                                </span>
                                                {log.after && (
                                                    <pre className="bg-thh-surface border-thh-border text-thh-text overflow-x-auto rounded-lg border p-2 font-mono text-[10px]">
                                                        {JSON.stringify(
                                                            log.after,
                                                            null,
                                                            2,
                                                        )}
                                                    </pre>
                                                )}
                                            </div>
                                        ))}
                                    </div>
                                )}
                            </div>
                        )}
                    </div>
                </div>
            </div>

            {/* Status Transition Modal */}
            {showTransitionModal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs">
                    <form
                        onSubmit={handleTransitionSubmit}
                        className="bg-thh-surface border-thh-border w-full max-w-md space-y-4 rounded-2xl border p-6 shadow-xl"
                    >
                        <h3 className="text-thh-text text-base font-bold">
                            Transition Case Status
                        </h3>

                        <div className="space-y-3 text-xs">
                            <div>
                                <label className="text-thh-text mb-1 block font-bold">
                                    New Status
                                </label>
                                <select
                                    value={transData.to_status}
                                    onChange={(e) =>
                                        setTransData(
                                            'to_status',
                                            e.target.value,
                                        )
                                    }
                                    className="border-thh-border bg-thh-bg text-thh-text w-full rounded-lg border px-3 py-2 font-bold"
                                >
                                    {allowedTransitions.map((t) => (
                                        <option
                                            key={t.to_status}
                                            value={t.to_status}
                                        >
                                            {t.to_status}
                                        </option>
                                    ))}
                                </select>
                            </div>

                            <div>
                                <label className="text-thh-text mb-1 block font-bold">
                                    Transition Note
                                </label>
                                <textarea
                                    value={transData.note}
                                    onChange={(e) =>
                                        setTransData('note', e.target.value)
                                    }
                                    placeholder="Explain reason for transition..."
                                    rows={3}
                                    className="border-thh-border bg-thh-bg text-thh-text w-full rounded-lg border px-3 py-2"
                                />
                            </div>

                            <div>
                                <label className="text-thh-text mb-1 block font-bold">
                                    Visibility
                                </label>
                                <select
                                    value={transData.visibility}
                                    onChange={(e) =>
                                        setTransData(
                                            'visibility',
                                            e.target.value,
                                        )
                                    }
                                    className="border-thh-border bg-thh-bg text-thh-text w-full rounded-lg border px-3 py-2"
                                >
                                    <option value="public">
                                        Public (Visible to Citizen on Mobile)
                                    </option>
                                    <option value="internal">
                                        Internal (Staff & Admin Only)
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div className="flex justify-end gap-3 pt-2">
                            <button
                                type="button"
                                onClick={() => setShowTransitionModal(false)}
                                className="border-thh-border text-thh-text rounded-lg border px-4 py-2 text-xs font-semibold"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                disabled={transProcessing}
                                className="bg-thh-primary rounded-lg px-4 py-2 text-xs font-bold text-white"
                            >
                                {transProcessing
                                    ? 'Saving...'
                                    : 'Apply Transition'}
                            </button>
                        </div>
                    </form>
                </div>
            )}

            {/* Internal Note Modal */}
            {showNoteModal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs">
                    <form
                        onSubmit={handleNoteSubmit}
                        className="bg-thh-surface border-thh-border w-full max-w-md space-y-4 rounded-2xl border p-6 shadow-xl"
                    >
                        <div className="border-thh-border flex items-center gap-2 border-b pb-3">
                            <Lock className="h-4 w-4 text-amber-500" />
                            <h3 className="text-thh-text text-base font-bold">
                                Add Strictly Internal Note
                            </h3>
                        </div>
                        <p className="text-thh-text-muted text-[11px]">
                            This note will never be accessible or visible to
                            citizens.
                        </p>

                        <div className="text-xs">
                            <textarea
                                value={noteData.note}
                                onChange={(e) =>
                                    setNoteData('note', e.target.value)
                                }
                                placeholder="Enter confidential verification or field observation..."
                                rows={4}
                                required
                                className="border-thh-border bg-thh-bg text-thh-text w-full rounded-lg border px-3 py-2"
                            />
                        </div>

                        <div className="flex justify-end gap-3 pt-2">
                            <button
                                type="button"
                                onClick={() => setShowNoteModal(false)}
                                className="border-thh-border text-thh-text rounded-lg border px-4 py-2 text-xs font-semibold"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                disabled={noteProcessing}
                                className="bg-thh-primary rounded-lg px-4 py-2 text-xs font-bold text-white"
                            >
                                {noteProcessing
                                    ? 'Saving...'
                                    : 'Save Internal Note'}
                            </button>
                        </div>
                    </form>
                </div>
            )}

            {/* Reassign Modal */}
            {showAssignModal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs">
                    <form
                        onSubmit={handleAssignSubmit}
                        className="bg-thh-surface border-thh-border w-full max-w-md space-y-4 rounded-2xl border p-6 shadow-xl"
                    >
                        <h3 className="text-thh-text text-base font-bold">
                            Reassign Case
                        </h3>

                        <div className="space-y-3 text-xs">
                            <div>
                                <label className="text-thh-text mb-1 block font-bold">
                                    Select Assignee
                                </label>
                                <select
                                    value={assignData.assignee_id}
                                    onChange={(e) =>
                                        setAssignData(
                                            'assignee_id',
                                            e.target.value,
                                        )
                                    }
                                    className="border-thh-border bg-thh-bg text-thh-text w-full rounded-lg border px-3 py-2 font-semibold"
                                >
                                    {availableStaff.map((st) => (
                                        <option key={st.id} value={st.id}>
                                            {st.name} (
                                            {st.assigned_applications_count ||
                                                0}{' '}
                                            active cases)
                                        </option>
                                    ))}
                                </select>
                            </div>

                            <div>
                                <label className="text-thh-text mb-1 block font-bold">
                                    Reason for Reassignment
                                </label>
                                <input
                                    type="text"
                                    value={assignData.reason}
                                    onChange={(e) =>
                                        setAssignData('reason', e.target.value)
                                    }
                                    placeholder="e.g. Workload rebalance for Dediapada"
                                    className="border-thh-border bg-thh-bg text-thh-text w-full rounded-lg border px-3 py-2"
                                />
                            </div>
                        </div>

                        <div className="flex justify-end gap-3 pt-2">
                            <button
                                type="button"
                                onClick={() => setShowAssignModal(false)}
                                className="border-thh-border text-thh-text rounded-lg border px-4 py-2 text-xs font-semibold"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                disabled={assignProcessing}
                                className="bg-thh-primary rounded-lg px-4 py-2 text-xs font-bold text-white"
                            >
                                {assignProcessing
                                    ? 'Reassigning...'
                                    : 'Assign Staff'}
                            </button>
                        </div>
                    </form>
                </div>
            )}
        </AdminLayout>
    );
}
