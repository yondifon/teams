import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import { Team, TeamInvitation, PageProps } from '@/types';

interface AcceptInvitationProps extends PageProps {
    invitation: TeamInvitation & {
        team: Team;
    };
}

export default function AcceptInvitation({ invitation }: AcceptInvitationProps) {
    const { post: acceptPost, processing: acceptProcessing } = useForm();
    const { post: declinePost, processing: declineProcessing } = useForm();

    const acceptInvitation: FormEventHandler = (e) => {
        e.preventDefault();
        acceptPost(route('team-invitations.process-accept', invitation.id));
    };

    const declineInvitation: FormEventHandler = (e) => {
        e.preventDefault();
        declinePost(route('team-invitations.decline', invitation.id));
    };

    return (
        <>
            <Head title="Team Invitation" />
            
            <div className="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
                <div className="sm:mx-auto sm:w-full sm:max-w-md">
                    <div className="flex justify-center">
                        <div className="flex h-12 w-12 items-center justify-center rounded-full bg-indigo-100">
                            <svg
                                className="h-6 w-6 text-indigo-600"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                aria-hidden="true"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                />
                            </svg>
                        </div>
                    </div>
                    <h2 className="mt-6 text-center text-3xl font-extrabold text-gray-900">
                        Join Team
                    </h2>
                    <p className="mt-2 text-center text-sm text-gray-600">
                        You've been invited to join the team
                    </p>
                </div>

                <div className="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
                    <div className="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
                        <div className="text-center mb-6">
                            <h3 className="text-lg font-medium text-gray-900 mb-2">
                                {invitation.team.name}
                            </h3>
                            <p className="text-sm text-gray-600">
                                You've been invited to join this team as{' '}
                                <span className="font-medium">
                                    {invitation.role?.name || 'Member'}
                                </span>
                            </p>
                        </div>

                        <div className="bg-gray-50 rounded-md p-4 mb-6">
                            <div className="text-sm">
                                <div className="font-medium text-gray-900">Team Details:</div>
                                <div className="text-gray-600 mt-1">
                                    <div>Name: {invitation.team.name}</div>
                                    {invitation.team.owner && (
                                        <div>Owner: {invitation.team.owner.name}</div>
                                    )}
                                    <div>Your Role: {invitation.role?.name || 'Member'}</div>
                                </div>
                            </div>
                        </div>

                        <div className="space-y-4">
                            <form onSubmit={acceptInvitation}>
                                <button
                                    type="submit"
                                    disabled={acceptProcessing || declineProcessing}
                                    className="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                                >
                                    {acceptProcessing ? 'Accepting...' : 'Accept Invitation'}
                                </button>
                            </form>

                            <form onSubmit={declineInvitation}>
                                <button
                                    type="submit"
                                    disabled={acceptProcessing || declineProcessing}
                                    className="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                                >
                                    {declineProcessing ? 'Declining...' : 'Decline Invitation'}
                                </button>
                            </form>
                        </div>

                        <div className="mt-6 text-center">
                            <p className="text-xs text-gray-500">
                                If you didn't expect this invitation, you can safely decline it.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}