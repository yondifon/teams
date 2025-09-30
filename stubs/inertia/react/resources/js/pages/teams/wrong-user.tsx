import { Head, Link } from '@inertiajs/react';
import { Team, TeamInvitation, PageProps } from '@/types';

interface WrongUserProps extends PageProps {
    invitation: TeamInvitation & {
        team: Team;
    };
    expectedEmail: string;
    currentEmail: string;
}

export default function WrongUser({ invitation, expectedEmail, currentEmail }: WrongUserProps) {
    return (
        <>
            <Head title="Wrong Account" />
            
            <div className="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
                <div className="sm:mx-auto sm:w-full sm:max-w-md">
                    <div className="flex justify-center">
                        <div className="flex h-12 w-12 items-center justify-center rounded-full bg-red-100">
                            <svg
                                className="h-6 w-6 text-red-600"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                aria-hidden="true"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"
                                />
                            </svg>
                        </div>
                    </div>
                    <h2 className="mt-6 text-center text-3xl font-extrabold text-gray-900">
                        Wrong Account
                    </h2>
                    <p className="mt-2 text-center text-sm text-gray-600">
                        You're signed in with the wrong account
                    </p>
                </div>

                <div className="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
                    <div className="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
                        <div className="text-center mb-6">
                            <h3 className="text-lg font-medium text-gray-900 mb-4">
                                Account Mismatch
                            </h3>
                            <div className="bg-red-50 border border-red-200 rounded-md p-4 mb-4">
                                <div className="text-sm text-red-800">
                                    <div className="font-medium mb-2">This invitation is for:</div>
                                    <div className="font-mono bg-red-100 px-2 py-1 rounded">
                                        {expectedEmail}
                                    </div>
                                </div>
                            </div>
                            <div className="bg-gray-50 border border-gray-200 rounded-md p-4">
                                <div className="text-sm text-gray-600">
                                    <div className="font-medium mb-2">You're currently signed in as:</div>
                                    <div className="font-mono bg-gray-100 px-2 py-1 rounded">
                                        {currentEmail}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="space-y-4">
                            <Link
                                href="/logout"
                                method="post"
                                as="button"
                                className="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                Sign Out & Continue
                            </Link>

                            <Link
                                href="/dashboard"
                                className="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                Go to Dashboard
                            </Link>
                        </div>

                        <div className="mt-6 text-center">
                            <p className="text-xs text-gray-500">
                                To accept this invitation, you need to be signed in with the email address {expectedEmail}.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}