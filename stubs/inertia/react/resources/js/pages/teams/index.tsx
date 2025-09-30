import { Head, Link } from '@inertiajs/react';
import { Team, PageProps } from '@/types';

interface TeamsIndexProps extends PageProps {
    teams: Team[];
    currentTeam: Team | null;
}

export default function Index({ teams, currentTeam }: TeamsIndexProps) {
    return (
        <>
            <Head title="Teams" />
            
            <div className="flex items-start max-md:flex-col">
                <div className="sticky top-0 me-10 w-full py-4 pb-4 md:w-[220px]">
                    <nav className="space-y-1">
                        <Link
                            href="/teams/create"
                            className="flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-50"
                        >
                            Create Team
                        </Link>
                    </nav>
                </div>
                
                <div className="flex-1 self-stretch py-4 max-md:pt-6">
                    <div className="mb-8">
                        <h1 className="text-2xl font-semibold text-gray-900">Teams</h1>
                        <p className="mt-1 text-sm text-gray-600">Manage your teams and create new ones.</p>
                    </div>

                    <div className="mt-5 w-full">
                        {teams.length > 0 ? (
                            <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                                {teams.map((team) => (
                                    <div key={team.id} className="bg-white rounded-lg border shadow-sm p-6">
                                        <div className="flex items-center justify-between mb-4">
                                            <h3 className="text-lg font-medium text-gray-900">{team.name}</h3>
                                            {team.personal_team && (
                                                <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    Personal
                                                </span>
                                            )}
                                        </div>

                                        <div className="text-sm text-gray-600 mb-4">
                                            {team.users?.length ? team.users.length + 1 : 1} members
                                        </div>

                                        <div className="flex gap-2">
                                            <Link
                                                href="/team"
                                                className="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                                            >
                                                View
                                            </Link>
                                            <Link
                                                href="/team/members"
                                                className="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                                            >
                                                Members
                                            </Link>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="text-center py-12">
                                <svg
                                    className="mx-auto h-12 w-12 text-gray-400"
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
                                <h3 className="mt-2 text-sm font-medium text-gray-900">No teams</h3>
                                <p className="mt-1 text-sm text-gray-500">Get started by creating a new team.</p>
                                <div className="mt-6">
                                    <Link
                                        href="/teams/create"
                                        className="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                    >
                                        Create Team
                                    </Link>
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </>
    );
}