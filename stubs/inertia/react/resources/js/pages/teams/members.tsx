import { Head, useForm, router } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';
import { Team, User, TeamInvitation, Role, PageProps } from '@/types';

interface TeamsMembersProps extends PageProps {
    team: Team & {
        owner: User;
        users: (User & { pivot: { role: Role } })[];
        invitations: TeamInvitation[];
    };
    roles: Role[];
    permissions: {
        canAddTeamMembers: boolean;
        canRemoveTeamMembers: boolean;
        canUpdateTeamMembers: boolean;
    };
}

export default function Members({ team, roles, permissions }: TeamsMembersProps) {
    const [confirmingMemberRemoval, setConfirmingMemberRemoval] = useState<number | null>(null);
    const [confirmingInvitationCancellation, setConfirmingInvitationCancellation] = useState<number | null>(null);

    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        role: roles[0]?.key || '',
    });

    const inviteTeamMember: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('team-members.store'), {
            onSuccess: () => reset(),
        });
    };

    const removeMember = (userId: number) => {
        router.delete(route('team-members.destroy', userId), {
            onFinish: () => setConfirmingMemberRemoval(null),
        });
    };

    const cancelInvitation = (invitationId: number) => {
        router.delete(route('team-invitations.cancel', invitationId), {
            onFinish: () => setConfirmingInvitationCancellation(null),
        });
    };

    const resendInvitation = (invitationId: number) => {
        router.post(route('team-invitations.resend', invitationId));
    };

    return (
        <>
            <Head title="Team Members" />
            
            <div className="max-w-4xl mx-auto py-8">
                <div className="mb-8">
                    <h1 className="text-2xl font-semibold text-gray-900">Team Members</h1>
                    <p className="mt-1 text-sm text-gray-600">Manage who has access to this team and their roles</p>
                </div>

                <div className="space-y-8">
                    {/* Add Member Form */}
                    {permissions.canAddTeamMembers && (
                        <div className="bg-white shadow rounded-lg">
                            <div className="px-6 py-4 border-b border-gray-200">
                                <h3 className="text-lg font-medium text-gray-900">Invite New Member</h3>
                                <p className="mt-1 text-sm text-gray-600">Send an invitation to add someone to your team</p>
                            </div>
                            
                            <form onSubmit={inviteTeamMember} className="p-6 space-y-6">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label htmlFor="email" className="block text-sm font-medium text-gray-700">
                                            Email Address
                                        </label>
                                        <div className="mt-1">
                                            <input
                                                id="email"
                                                type="email"
                                                value={data.email}
                                                onChange={(e) => setData('email', e.target.value)}
                                                className="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                placeholder="Enter email address"
                                                required
                                            />
                                        </div>
                                        {errors.email && (
                                            <div className="mt-2 text-sm text-red-600">{errors.email}</div>
                                        )}
                                    </div>
                                    
                                    <div>
                                        <label htmlFor="role" className="block text-sm font-medium text-gray-700">
                                            Role
                                        </label>
                                        <div className="mt-1">
                                            <select
                                                id="role"
                                                value={data.role}
                                                onChange={(e) => setData('role', e.target.value)}
                                                className="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                required
                                            >
                                                {roles.map((role) => (
                                                    <option key={role.key} value={role.key}>
                                                        {role.name}
                                                    </option>
                                                ))}
                                            </select>
                                        </div>
                                        {errors.role && (
                                            <div className="mt-2 text-sm text-red-600">{errors.role}</div>
                                        )}
                                    </div>
                                </div>

                                <div className="flex items-center justify-end pt-2">
                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                                    >
                                        {processing ? 'Sending...' : 'Send Invite'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    )}

                    {/* Pending Invitations */}
                    {team.invitations && team.invitations.length > 0 && (
                        <div className="bg-white shadow rounded-lg">
                            <div className="px-6 py-4 border-b border-gray-200">
                                <div className="flex items-center justify-between">
                                    <div>
                                        <h3 className="text-lg font-medium text-gray-900">Pending Invitations</h3>
                                        <p className="mt-1 text-sm text-gray-600">Invitations waiting for acceptance</p>
                                    </div>
                                    <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        {team.invitations.length} pending
                                    </span>
                                </div>
                            </div>
                            
                            <div className="divide-y divide-gray-100">
                                {team.invitations.map((invitation) => (
                                    <div key={invitation.id} className="p-6 hover:bg-gray-50 transition-colors">
                                        <div className="flex items-center justify-between">
                                            <div className="flex items-center">
                                                <div className="flex h-12 w-12 items-center justify-center rounded-full bg-yellow-100">
                                                    <svg className="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </div>
                                                <div className="ml-4">
                                                    <div className="text-sm font-medium text-gray-900">{invitation.email}</div>
                                                    <div className="text-sm text-gray-500">
                                                        Invited {new Date(invitation.created_at).toLocaleDateString()}
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div className="flex items-center space-x-3">
                                                <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    {invitation.role?.name || 'Member'}
                                                </span>
                                                <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    Pending
                                                </span>
                                                
                                                {permissions.canAddTeamMembers && (
                                                    <button
                                                        onClick={() => resendInvitation(invitation.id)}
                                                        className="text-sm text-indigo-600 hover:text-indigo-900"
                                                    >
                                                        Resend
                                                    </button>
                                                )}
                                                
                                                {permissions.canRemoveTeamMembers && (
                                                    <button
                                                        onClick={() => setConfirmingInvitationCancellation(invitation.id)}
                                                        className="text-sm text-red-600 hover:text-red-900"
                                                    >
                                                        Cancel
                                                    </button>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}

                    {/* Current Members */}
                    <div className="bg-white shadow rounded-lg">
                        <div className="px-6 py-4 border-b border-gray-200">
                            <div className="flex items-center justify-between">
                                <div>
                                    <h3 className="text-lg font-medium text-gray-900">Current Members</h3>
                                    <p className="mt-1 text-sm text-gray-600">People who have access to this team</p>
                                </div>
                                <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {team.users.length} members
                                </span>
                            </div>
                        </div>
                        
                        <div className="divide-y divide-gray-100">
                            {team.users.map((member) => (
                                <div key={member.id} className="p-6 hover:bg-gray-50 transition-colors">
                                    <div className="flex items-center justify-between">
                                        <div className="flex items-center">
                                            <div className="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100">
                                                <span className="text-sm font-medium text-gray-700">
                                                    {member.name?.charAt(0)?.toUpperCase() || '?'}
                                                </span>
                                            </div>
                                            <div className="ml-4">
                                                <div className="text-sm font-medium text-gray-900">{member.name}</div>
                                                <div className="text-sm text-gray-500">{member.email}</div>
                                            </div>
                                        </div>
                                        
                                        <div className="flex items-center space-x-3">
                                            <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                {member.pivot.role?.name || 'member'}
                                            </span>
                                            
                                            {member.id === team.owner.id ? (
                                                <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    Owner
                                                </span>
                                            ) : permissions.canRemoveTeamMembers ? (
                                                <button
                                                    onClick={() => setConfirmingMemberRemoval(member.id)}
                                                    className="text-sm text-red-600 hover:text-red-900"
                                                >
                                                    Remove
                                                </button>
                                            ) : null}
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>

                {/* Remove Member Confirmation Modal */}
                {confirmingMemberRemoval && (
                    <div className="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50">
                        <div className="fixed inset-0 z-10 overflow-y-auto">
                            <div className="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                                <div className="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                                    <div className="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                                        <div className="sm:flex sm:items-start">
                                            <div className="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                                <svg className="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor">
                                                    <path strokeLinecap="round" strokeLinejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                                </svg>
                                            </div>
                                            <div className="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                                <h3 className="text-base font-semibold leading-6 text-gray-900">Remove Team Member</h3>
                                                <div className="mt-2">
                                                    <p className="text-sm text-gray-500">
                                                        Are you sure you want to remove this member from the team?
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                        <button
                                            type="button"
                                            onClick={() => removeMember(confirmingMemberRemoval)}
                                            className="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto"
                                        >
                                            Remove
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() => setConfirmingMemberRemoval(null)}
                                            className="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
                                        >
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                )}

                {/* Cancel Invitation Confirmation Modal */}
                {confirmingInvitationCancellation && (
                    <div className="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50">
                        <div className="fixed inset-0 z-10 overflow-y-auto">
                            <div className="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                                <div className="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                                    <div className="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                                        <div className="sm:flex sm:items-start">
                                            <div className="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                                <svg className="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor">
                                                    <path strokeLinecap="round" strokeLinejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                                </svg>
                                            </div>
                                            <div className="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                                <h3 className="text-base font-semibold leading-6 text-gray-900">Cancel Invitation</h3>
                                                <div className="mt-2">
                                                    <p className="text-sm text-gray-500">
                                                        Are you sure you want to cancel this invitation?
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                        <button
                                            type="button"
                                            onClick={() => cancelInvitation(confirmingInvitationCancellation)}
                                            className="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto"
                                        >
                                            Cancel Invitation
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() => setConfirmingInvitationCancellation(null)}
                                            className="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
                                        >
                                            Keep Invitation
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </>
    );
}