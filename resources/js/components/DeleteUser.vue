<script setup lang="ts">
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Form } from '@inertiajs/vue3';
import { useTemplateRef } from 'vue';

const passwordInput = useTemplateRef('passwordInput');
</script>

<template>
    <div class="space-y-6">
        <Heading variant="small" title="アカウントの削除" description="アカウントとそのすべてのデータを削除します" />
        <div class="space-y-4 rounded-lg border border-red-100 bg-red-50 p-4 dark:border-red-200/10 dark:bg-red-700/10">
            <div class="relative space-y-0.5 text-red-600 dark:text-red-100">
                <p class="font-medium">警告</p>
                <p class="text-sm">
                    この操作は取り消せません。慎重に進めてください。
                </p>
            </div>
            <Dialog>
                <DialogTrigger as-child>
                    <Button variant="destructive" data-test="delete-user-button">アカウントを削除する</Button>
                </DialogTrigger>
                <DialogContent>
                    <Form v-bind="ProfileController.destroy.form()" reset-on-success
                        @error="() => passwordInput?.$el?.focus()" :options="{
                            preserveScroll: true,
                        }" class="space-y-6" v-slot="{ errors, processing, reset, clearErrors }">
                        <DialogHeader class="space-y-3">
                            <DialogTitle>アカウントを削除してもよろしいですか？</DialogTitle>
                            <DialogDescription>
                                アカウントを削除すると、そのすべてのリソースとデータが完全に削除されます。
                                アカウントの完全な削除を確定するには、パスワードを入力してください。
                            </DialogDescription>
                        </DialogHeader>

                        <div class="grid gap-2">
                            <Label for="password" class="sr-only">パスワード</Label>
                            <Input id="password" type="password" name="password" ref="passwordInput"
                                placeholder="パスワードを入力してください" />
                            <InputError :message="errors.password" />
                        </div>

                        <DialogFooter class="gap-2">
                            <DialogClose as-child>
                                <Button variant="secondary" @click="
                                    () => {
                                        clearErrors();
                                        reset();
                                    }
                                ">
                                    キャンセル
                                </Button>
                            </DialogClose>

                            <Button type="submit" variant="destructive" :disabled="processing"
                                data-test="confirm-delete-user-button">
                                アカウントを削除
                            </Button>
                        </DialogFooter>
                    </Form>
                </DialogContent>
            </Dialog>
        </div>
    </div>
</template>