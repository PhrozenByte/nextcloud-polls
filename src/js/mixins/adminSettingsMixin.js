/* jshint esversion: 6 */
/**
 * @copyright Copyright (c) 2021 René Gieling <github@dartcafe.de>
 *
 * @author René Gieling <github@dartcafe.de>
 *
 * @license  AGPL-3.0-or-later
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

import debounce from 'lodash/debounce'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { mapState } from 'vuex'

export const loadGroups = {
	data() {
		return {
			searchToken: null,
			groups: [],
			isLoading: false,
		}
	},
	computed: {
		...mapState({
			appSettings: (state) => state.appSettings,
		}),
	},

	created() {
		this.loadGroups('')
	},

	methods: {
		loadGroups: debounce(async function(query) {
			let endPoint = 'apps/polls/groups'

			if (query.trim()) {
				endPoint = `${endPoint}/${query}`
			}

			this.isLoading = true

			if (this.searchToken) {
				this.searchToken.cancel()
			}

			this.searchToken = axios.CancelToken.source()

			try {
				const response = await axios.get(generateUrl(endPoint), {
					headers: { Accept: 'application/json' },
					cancelToken: this.searchToken.token,
				})
				this.groups = response.data.groups
				this.isLoading = false
			} catch (e) {
				if (axios.isCancel(e)) {
					// request was cancelled
				} else {
					console.error(e.response)
					this.isLoading = false
				}
			}
		}, 250),
	},
}

export const writeValue = {
	methods: {
		async writeValue(value) {
			await this.$store.commit('appSettings/set', value)
			this.$store.dispatch('appSettings/write')
		},
	},
}
