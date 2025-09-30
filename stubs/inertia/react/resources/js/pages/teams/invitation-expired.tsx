import { Head, Link } from '@inertiajs/react';
import { PageProps } from '@/types';

export default function InvitationExpired({}: PageProps) {
    return (
        <>
            <Head title="Invitation Expired" />
            
            <div className="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
                <div className="sm:mx-auto sm:w-full sm:max-w-md">
                    <div className="flex justify-center">
                        <div className="flex h-12 w-12 items-center justify-center rounded-full bg-yellow-100">
                            <svg
                                className="h-6 w-6 text-yellow-600"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                aria-hidden="true"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                        </div>
                    </div>
                    <h2 className="mt-6 text-center text-3xl font-extrabold text-gray-900">
                        Invitation Expired
                    </h2>
                    <p className="mt-2 text-center text-sm text-gray-600">
                        This team invitation is no longer valid
                    </p>
                </div>

                <div className="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
                    <div className="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
                        <div className="text-center">
                            <div className="mb-6">
                                <svg
                                    className="mx-auto h-16 w-16 text-gray-400"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    aria-hidden="true"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={1}
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                            </div>
                            
                            <h3 className="text-lg font-medium text-gray-900 mb-4">
                                Time's Up!
                            </h3>
                            
                            <div className="bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-6">
                                <p className="text-sm text-yellow-800">
                                    This team invitation has expired and is no longer valid. 
                                    Team invitations are only valid for a limited time for security reasons.
                                </p>
                            </div>

                            <div className="space-y-4">
                                <Link
                                    href="/dashboard"
                                    className="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    Go to Dashboard
                                </Link>

                                <Link
                                    href="/"
                                    className="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    Go to Home
                                </Link>
                            </div>

                            <div className="mt-6">
                                <p className="text-xs text-gray-500">
                                    If you still want to join this team, please contact the team owner 
                                    to send you a new invitation.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}