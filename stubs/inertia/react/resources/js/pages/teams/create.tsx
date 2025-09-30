import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import { PageProps } from '@/types';

export default function Create({}: PageProps) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('teams.store'));
    };

    return (
        <>
            <Head title="Create Team" />
            
            <div className="max-w-2xl mx-auto py-8">
                <div className="mb-8">
                    <h1 className="text-2xl font-semibold text-gray-900">Create Team</h1>
                    <p className="mt-1 text-sm text-gray-600">Create a new team to collaborate with others.</p>
                </div>

                <div className="bg-white shadow rounded-lg">
                    <form onSubmit={submit} className="space-y-6 p-6">
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
                                    placeholder="Enter team name"
                                    autoFocus
                                    required
                                />
                            </div>
                            {errors.name && (
                                <div className="mt-2 text-sm text-red-600">{errors.name}</div>
                            )}
                        </div>

                        <div className="flex items-center justify-end space-x-4 pt-4 border-t">
                            <Link
                                href="/dashboard"
                                className="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                disabled={processing}
                                className="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                            >
                                {processing ? 'Creating...' : 'Create Team'}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </>
    );
}