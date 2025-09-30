import { Head, useForm, router } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';
import { Team, User, PageProps } from '@/types';

interface TeamsShowProps extends PageProps {
    team: Team & {
        owner: User;
    };
    permissions: {
        canUpdateTeam: boolean;
        canDeleteTeam: boolean;
    };
}

export default function Show({ team, permissions }: TeamsShowProps) {
    const [confirmingTeamDeletion, setConfirmingTeamDeletion] = useState(false);

    const { data, setData, put, processing, errors, reset } = useForm({
        name: team.name,
    });

    const updateTeam: FormEventHandler = (e) => {
        e.preventDefault();

        put(route('teams.update'), {
            onSuccess: () => reset(),
        });
    };

    const deleteTeam = () => {
        router.delete(route('teams.destroy'), {
            onFinish: () => setConfirmingTeamDeletion(false),
        });
    };

    return (
        <>
            <Head title="Team Settings" />
            
            <div className="max-w-4xl mx-auto py-8">
                <div className="mb-8">
                    <h1 className="text-2xl font-semibold text-gray-900">Team Settings</h1>
                    <p className="mt-1 text-sm text-gray-600">Manage your team information and preferences</p>
                </div>

                <div className="space-y-6">
                    {/* Team Information */}
                    {permissions.canUpdateTeam && (
                        <div className="bg-white shadow rounded-lg">
                            <div className="px-6 py-4 border-b border-gray-200">
                                <h3 className="text-lg font-medium text-gray-900">Team Information</h3>
                                <p className="mt-1 text-sm text-gray-600">Update your team's name and details.</p>
                            </div>
                            
                            <form onSubmit={updateTeam} className="p-6 space-y-6">
                                <div>
                                    <label htmlFor="name" className="block text-sm font-medium text-gray-700">
                                        Team Name
                                    </label>
                                    <div className="mt-1">
                                        <input
                                            id="name"
                                            type="text"
                                            value={data.name}
                                            onChange={(e) => setData('name', e.target.value)}
                                            className="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                            required
                                            autoFocus
                                        />
                                    </div>
                                    {errors.name && (
                                        <div className="mt-2 text-sm text-red-600">{errors.name}</div>
                                    )}
                                </div>

                                <div className="flex items-center justify-end">
                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                                    >
                                        {processing ? 'Saving...' : 'Save'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    )}

                    {/* Team Owner */}
                    <div className="bg-white shadow rounded-lg">
                        <div className="px-6 py-4 border-b border-gray-200">
                            <h3 className="text-lg font-medium text-gray-900">Team Owner</h3>
                            <p className="mt-1 text-sm text-gray-600">The person who created and owns this team.</p>
                        </div>
                        
                        <div className="p-6">
                            <div className="flex items-center">
                                <div className="flex-shrink-0 h-10 w-10">
                                    <div className="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                        <span className="text-sm font-medium text-gray-700">
                                            {team.owner.name?.charAt(0)?.toUpperCase() || '?'}
                                        </span>
                                    </div>
                                </div>
                                <div className="ml-4">
                                    <div className="text-sm font-medium text-gray-900">{team.owner.name}</div>
                                    <div className="text-sm text-gray-500">{team.owner.email}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Delete Team */}
                    {permissions.canDeleteTeam && !team.personal_team && (
                        <div className="bg-white shadow rounded-lg">
                            <div className="px-6 py-4 border-b border-gray-200">
                                <h3 className="text-lg font-medium text-red-900">Delete Team</h3>
                                <p className="mt-1 text-sm text-gray-600">Permanently delete this team and all of its data.</p>
                            </div>
                            
                            <div className="p-6">
                                <button
                                    type="button"
                                    onClick={() => setConfirmingTeamDeletion(true)}
                                    className="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                >
                                    Delete Team
                                </button>
                            </div>
                        </div>
                    )}
                </div>

                {/* Delete Confirmation Modal */}
                {confirmingTeamDeletion && (
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
                                                <h3 className="text-base font-semibold leading-6 text-gray-900">Delete Team</h3>
                                                <div className="mt-2">
                                                    <p className="text-sm text-gray-500">
                                                        Are you sure you want to delete this team? This action cannot be undone.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                        <button
                                            type="button"
                                            onClick={deleteTeam}
                                            className="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto"
                                        >
                                            Delete
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() => setConfirmingTeamDeletion(false)}
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
            </div>
        </>
    );
}